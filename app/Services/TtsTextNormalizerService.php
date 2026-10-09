<?php

namespace App\Services;

class TtsTextNormalizerService
{
    /**
     * Checks if the text exhibits Masonic / symbolic context.
     */
    public function isMasonicText(string $text): bool
    {
        // 1. Unicode delta (∴), middle-dot tripods (.·.), two-dot tripods (.. .) or :.
        if (preg_match('/[∴]/u', $text) || preg_match('/\.\s*·\s*\./u', $text) || preg_match('/\.\.\s*\./u', $text) || preg_match('/[:.]{2,3}/u', $text)) {
            return true;
        }

        // 2. Typical Masonic acronyms and ritual formulas (requiring explicit dots or all-caps GADU)
        if (preg_match('/\b(GADU|G\.A\.D\.U\.|S\.F\.U\.|T\.A\.F\.|L\.I\.F\.|R\.E\.A\.A\.|I\.P\.H\.|M\.R\.G\.M\.)\b/u', $text) ||
            preg_match('/\bA\s*∴\s*L\s*∴?\s*\d{4}\b/u', $text)) {
            return true;
        }

        // 3. Parenthesis-based Masonic abbreviations: require paired ritual formulas, not isolated single letters
        if (preg_match('/(?:A\s*\(\s*L\s*\(|Ven\s*\(\s*M\s*\(|Q\s*\(\s*H\s*\(|QQ\s*\(\s*HH\s*\(|Resp\s*\(\s*Log\s*\(|VVig\s*\(|M\s*\(\s*M\s*\(|S\s*\(\s*F\s*\(\s*U\s*\()/iu', $text)) {
            return true;
        }

        // 4. Dot-colon or colon-dot paired abbreviations (Q:. H:., V:. M:.)
        if (preg_match('/[A-ZÁÉÍÓÚÑ]{1,4}\s*[:.·]{2,4}\s*[A-ZÁÉÍÓÚÑ]{1,4}\s*[:.·]{2,4}/iu', $text)) {
            return true;
        }

        // 5. Explicit symbolic and ritual full multi-word formulas
        if (preg_match('/\b(venerable\s+maestro|querido\s+hermano|queridos\s+hermanos|gran\s+arquitecto\s+del\s+universo|respetable\s+logia\s+simb[oó]lica|francmasoner[ií]a|salud,\s*fuerza\s*y\s*uni[oó]n|triple\s+abrazo\s+fraternal|abreviatura\s+tripuntuada|c[aá]mara\s+del\s+medio|templo\s+mas[oó]nico)\b/iu', $text)) {
            return true;
        }

        return false;
    }

    /**
     * Reconstructs authentic Masonic tripunctuated text (∴) from degraded representations
     * (e.g. legacy Word parentheses '(', middle-dots '.·.', or two-dot tripods '.. .').
     */
    public function reconstructTripunctuatedText(string $text): string
    {
        if (! $this->isMasonicText($text)) {
            return $text;
        }

        // Unify .·. or .. . or :. or .:. into ∴
        $text = preg_replace('/([A-Za-z0-9ÁÉÍÓÚÑáéíóúñ])\s*(?:\.\s*·\s*\.|\.\.\s*\.|[:.]{2,3})\s*/u', '$1∴ ', $text);

        // Unify legacy font parentheses '(' into ∴
        $text = preg_replace('/\b(A|L|G|D|U|I|Ven|Q|QQ|H|HH|VVig|Vig|VVisit|Visit|S|F|E|V|M|MM|Or|Resp|Log|Secr|Orad|Tes|Hosp|Exp|Prof|Proff|Di[aá]c|DDi[aá]c|Planch|Plza)\s*\(\s*/iu', '$1∴ ', $text);

        return $text;
    }

    /**
     * Normalizes text for high-fidelity Natural TTS playback.
     */
    public function normalize(string $text, ?bool $forceMasonic = null): string
    {
        if (empty(trim($text))) {
            return '';
        }

        // Standardize line endings and non-breaking spaces
        $text = str_replace(["\r\n", "\r", "\xc2\xa0"], ["\n", "\n", ' '], $text);

        // Remove [pic], [image], [imagen] artifacts
        $text = preg_replace('/\[(?:pic|image|imagen)\]/i', '', $text);

        // Clean Markdown syntax so headers, bold, bullets and links speak naturally without reading symbols
        $text = $this->cleanMarkdownForSpeech($text);

        // Check if Masonic mode should be engaged
        $isMasonic = $forceMasonic ?? $this->isMasonicText($text);

        if ($isMasonic) {
            // Reconstruct tripunctuated representation first for consistent matching
            $text = $this->reconstructTripunctuatedText($text);
            $text = $this->expandMasonicAbbreviations($text);
        }

        // General formatting and symbols cleanup
        $text = $this->expandGeneralSymbolsAndAbbreviations($text);

        // Sentence continuity and soft line breaks
        $text = $this->bridgeSentenceContinuity($text);

        return trim($text);
    }

    /**
     * Sanitizes Markdown syntax for fluent, natural TTS speech synthesis:
     * - Strips '#' headers and ensures punctuation pause
     * - Converts links [text](url) to just 'text'
     * - Removes code fences, backticks, bold/italic asterisks, and blockquote '>' symbols
     * - Cleans unordered list bullets (*, -, +)
     */
    public function cleanMarkdownForSpeech(string $text): string
    {
        // 1. Markdown Headers: '# Header', '## Subheader', etc.
        // Strip '#' symbols and ensure sentence pause (. or :) at the end so it doesn't rush into next line
        $text = preg_replace_callback('/^[ \t]*#{1,6}[ \t]+([^\n\r]+)$/m', function ($matches) {
            $heading = trim($matches[1]);
            if (! preg_match('/[.:!?]$/u', $heading)) {
                $heading .= '.';
            }

            return $heading;
        }, $text);

        // 2. Markdown Links: '[Visible Title](https://example.com)' -> 'Visible Title'
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', $text);

        // 3. Code Blocks: ```lang ... ``` and inline code: `code`
        $text = preg_replace('/```[a-zA-Z0-9_-]*\n?([\s\S]*?)```/u', '$1', $text);
        $text = preg_replace('/`([^`]+)`/u', '$1', $text);

        // 4. Blockquotes: '> Quotation text' -> 'Quotation text'
        $text = preg_replace('/^[ \t]*>[ \t]*/m', '', $text);

        // 5. Strikethrough: ~~text~~ -> text
        $text = preg_replace('/~~([^~]+)~~/u', '$1', $text);

        // 6. Bold and Italic: **bold**, *italic*, __bold__, _italic_
        $text = preg_replace('/(?:\*\*|__)(.*?)(?:\*\*|__)/u', '$1', $text);
        $text = preg_replace('/(?:\*|_)(.*?)(?:\*|_)/u', '$1', $text);

        // 7. Unordered Lists: '- Item', '* Item', '+ Item'
        $text = preg_replace('/^[ \t]*[-*+][ \t]+/m', '', $text);

        // 8. Markdown Horizontal Dividers: '---', '***', '___'
        $text = preg_replace('/^[ \t]*[-*_]{3,}[ \t]*$/m', '', $text);

        // 9. Markdown Tables: strip divider rows |---|---| and remove pipes |
        $text = preg_replace('/^[ \t]*\|[ \t]*:?[-]+:?[ \t]*(?:\|[ \t]*:?[-]+:?[ \t]*)+\|[ \t]*$/m', '', $text);
        $text = preg_replace_callback('/^[ \t]*\|(.+)\|[ \t]*$/m', function ($matches) {
            $cells = explode('|', $matches[1]);
            $cleaned = array_filter(array_map('trim', $cells));

            return implode(', ', $cleaned).'.';
        }, $text);

        return $text;
    }

    /**
     * Expands ritual and symbolic Masonic abbreviations into solemn, clear words
     * according to the canonical manuals (Gran Logia de Venezuela, Sebastianelli, Frau Abrines).
     */
    public function expandMasonicAbbreviations(string $text): string
    {
        // 1. Invocation & Landmark formulas
        $text = preg_replace('/A\s*L\s*∴\s*G\s*∴\s*D\s*∴\s*G\s*∴\s*A\s*∴\s*D\s*∴\s*U\s*∴?/iu', 'A la Gloria del Gran Arquitecto del Universo', $text);
        $text = preg_replace('/\b(?:G\s*∴\s*A\s*∴\s*D\s*∴\s*U\s*∴?|GADU)\b/iu', 'Gran Arquitecto del Universo', $text);
        $text = preg_replace('/\bS\s*∴\s*F\s*∴\s*U\s*∴?/iu', 'Salud, Fuerza y Unión', $text);
        $text = preg_replace('/\bT\s*∴\s*A\s*∴\s*F\s*∴?/iu', 'Triple Abrazo Fraternal', $text);
        $text = preg_replace('/\bL\s*∴\s*I\s*∴\s*F\s*∴?/iu', 'Libertad, Igualdad, Fraternidad', $text);
        $text = preg_replace('/\bE\s*∴\s*V\s*∴?/iu', 'Era Vulgar', $text);
        $text = preg_replace('/\bA\s*∴\s*L\s*∴?\s*(\d{4})/iu', 'Año de la Verdadera Luz $1', $text);
        $text = preg_replace('/\bA\s*∴\s*L\s*∴?/iu', 'Año de la Verdadera Luz', $text);
        $text = preg_replace('/\bA\s*∴\s*F\s*∴?/iu', 'Antiguo Francmasón', $text);
        $text = preg_replace('/\bA\s*∴\s*H\s*∴?/iu', 'Año Hebreo', $text);
        $text = preg_replace('/\bss\s*∴\s*pp\s*∴\s*tt\s*∴?/iu', 'signos, palabras y tocamientos', $text);

        // 2. Grados y Jerarquías Compuestas (Debe preceder a iniciales aisladas)
        $text = preg_replace('/\bMM\s*∴\s*MM\s*∴?/iu', 'Maestros Masones ', $text);
        $text = preg_replace('/\bM\s*∴\s*M\s*∴?/iu', 'Maestro Masón ', $text);
        $text = preg_replace('/\bAA\s*∴\s*MM\s*∴?/iu', 'Aprendices Masones ', $text);
        $text = preg_replace('/\bCC\s*∴\s*MM\s*∴?/iu', 'Compañeros Masones ', $text);

        // 3. Obediencias, Ritos y Tratamientos Supremos (Mayor longitud primero)
        $text = preg_replace('/\bR\s*∴\s*E\s*∴\s*A\s*∴\s*A\s*∴?/iu', 'Rito Escocés Antiguo y Aceptado', $text);
        $text = preg_replace('/\bG\s*∴\s*L\s*∴\s*R\s*∴\s*V\s*∴?/iu', 'Gran Logia de la República de Venezuela', $text);
        $text = preg_replace('/\bU\s*∴\s*G\s*∴\s*L\s*∴\s*E\s*∴?/iu', 'Gran Logia Unida de Inglaterra', $text);
        $text = preg_replace('/\bG\s*∴\s*O\s*∴\s*F\s*∴?/iu', 'Gran Oriente de Francia', $text);
        $text = preg_replace('/\bM\s*∴\s*R\s*∴\s*G\s*∴\s*M\s*∴?/iu', 'Muy Respetable Gran Maestro ', $text);
        $text = preg_replace('/\bM\s*∴\s*R\s*∴(?!esp)/iu', 'Muy Respetable ', $text);
        $text = preg_replace('/\bI\s*∴\s*P\s*∴\s*H\s*∴?/iu', 'Ilustre y Poderoso Hermano ', $text);
        $text = preg_replace('/\bII\s*∴\s*PP\s*∴\s*HH\s*∴?/iu', 'Ilustres y Poderosos Hermanos ', $text);
        $text = preg_replace('/\bV\s*∴\s*H\s*∴?/iu', 'Venerable Hermano ', $text);
        $text = preg_replace('/\bVV\s*∴\s*HH\s*∴?/iu', 'Venerables Hermanos ', $text);

        // 4. Venerables y Jerarquías
        $text = preg_replace('/\bEx\s*[-–]\s*Ven\s*∴\s*M\s*∴?/iu', 'Ex Venerable Maestro ', $text);
        $text = preg_replace('/\bVen\s*∴\s*M\s*∴?/iu', 'Venerable Maestro ', $text);
        $text = preg_replace('/\bV\s*∴\s*M\s*∴?/iu', 'Venerable Maestro ', $text);
        $text = preg_replace('/\bGr\s*∴\s*M\s*∴?/iu', 'Gran Maestro ', $text);
        $text = preg_replace('/\bG\s*∴\s*M\s*∴?/iu', 'Gran Maestro ', $text);

        // 5. Logias, Orientes y Talleres
        $text = preg_replace('/\bResp\s*∴\s*Log\s*∴\s*Simb\s*∴?/iu', 'Respetable Logia Simbólica ', $text);
        $text = preg_replace('/\bR\s*∴\s*L\s*∴\s*S\s*∴?/iu', 'Respetable Logia Simbólica ', $text);
        $text = preg_replace('/\bResp\s*∴\s*Log\s*∴?/iu', 'Respetable Logia ', $text);
        $text = preg_replace('/\bR\s*∴\s*L\s*∴?/iu', 'Respetable Logia ', $text);
        $text = preg_replace('/\bLLog\s*∴?/iu', 'Logias ', $text);
        $text = preg_replace('/\bLL\s*∴?/iu', 'Logias ', $text);
        $text = preg_replace('/\bGr\s*∴\s*Log\s*∴?/iu', 'Gran Logia ', $text);
        $text = preg_replace('/\bG\s*∴\s*L\s*∴?/iu', 'Gran Logia ', $text);
        $text = preg_replace('/\bGr\s*∴\s*Or\s*∴?/iu', 'Gran Oriente ', $text);
        $text = preg_replace('/\bG\s*∴\s*O\s*∴?/iu', 'Gran Oriente ', $text);
        $text = preg_replace('/\bTTall\s*∴?/iu', 'Talleres ', $text);
        $text = preg_replace('/\bTT\s*∴?/iu', 'Talleres ', $text);

        // 5. Hermanos y Saludos
        $text = preg_replace('/\bQQ\s*∴\s*HH\s*∴\s*Todos\b/iu', 'Queridos Hermanos Todos ', $text);
        $text = preg_replace('/\bQQ\s*∴\s*HH\s*∴\s*1[º°]\s+y\s+2[º°]\s+VVig\s*∴?/iu', 'Queridos Hermanos Primer y Segundo Vigilantes ', $text);
        $text = preg_replace('/\bQQ\s*∴\s*HH\s*∴\s*VVisit\s*∴?/iu', 'Queridos Hermanos Visitadores ', $text);
        $text = preg_replace('/\bQQ\s*∴\s*HH\s*∴?/iu', 'Queridos Hermanos ', $text);
        $text = preg_replace('/\bQ\s*∴\s*H\s*∴?/iu', 'Querido Hermano ', $text);
        $text = preg_replace('/\bHH\s*∴/iu', 'Hermanos ', $text);
        $text = preg_replace('/\bH\s*∴\s+(?=[A-ZÁÉÍÓÚÑ])/u', 'Hermano ', $text);

        // 6. Oficiales y Dignidades
        $text = preg_replace('/\b1[º°]\s+y\s+2[º°]\s+VVig\s*∴?/iu', 'Primer y Segundo Vigilantes ', $text);
        $text = preg_replace('/\b1[º°]\s+Vig\s*∴?/iu', 'Primer Vigilante ', $text);
        $text = preg_replace('/\b2[º°]\s+Vig\s*∴?/iu', 'Segundo Vigilante ', $text);
        $text = preg_replace('/\bP\s*∴\s*Vig\s*∴?/iu', 'Primer Vigilante ', $text);
        $text = preg_replace('/\bS\s*∴\s*Vig\s*∴?/iu', 'Segundo Vigilante ', $text);
        $text = preg_replace('/\bVVig\s*∴?(?![a-zA-ZáéíóúÁÉÍÓÚñÑ])/iu', 'Vigilantes ', $text);
        $text = preg_replace('/\bVig\s*∴?(?![a-zA-ZáéíóúÁÉÍÓÚñÑ])/iu', 'Vigilante ', $text);
        $text = preg_replace('/\bVVisit\s*∴?/iu', 'Visitadores ', $text);
        $text = preg_replace('/\bOrad\s*∴?/iu', 'Orador ', $text);
        $text = preg_replace('/\bSecr\s*∴?/iu', 'Secretario ', $text);
        $text = preg_replace('/\bTes\s*∴?/iu', 'Tesorero ', $text);
        $text = preg_replace('/\bHosp\s*∴?/iu', 'Hospitalario ', $text);
        $text = preg_replace('/\bMM\s*∴\s*CC\s*∴?/iu', 'Maestros de Ceremonias ', $text);
        $text = preg_replace('/\bM\s*∴\s*C\s*∴?/iu', 'Maestro de Ceremonias ', $text);
        $text = preg_replace('/\bM\s*∴\s*de\s+Cer\s*∴?/iu', 'Maestro de Ceremonias ', $text);
        $text = preg_replace('/\bG\s*∴\s*T\s*∴\s*I\s*∴?/iu', 'Guarda Templo Interior ', $text);
        $text = preg_replace('/\bG\s*∴\s*T\s*∴\s*E\s*∴?/iu', 'Guarda Templo Exterior ', $text);
        $text = preg_replace('/\bG\s*∴\s*T\s*∴?/iu', 'Guarda Templo ', $text);
        $text = preg_replace('/\bEExp\s*∴?/iu', 'Expertos ', $text);
        $text = preg_replace('/\bExp\s*∴?/iu', 'Experto ', $text);
        $text = preg_replace('/\bDDi[aá]c\s*∴?/iu', 'Diáconos ', $text);
        $text = preg_replace('/\bDi[aá]c\s*∴?/iu', 'Diácono ', $text);

        // 7. Grados y Plurales
        $text = preg_replace('/\bAA\s*∴\s*MM\s*∴?/iu', 'Aprendices Masones ', $text);
        $text = preg_replace('/\bAAp\s*∴?/iu', 'Aprendices ', $text);
        $text = preg_replace('/\bAp\s*∴\s*M\s*∴?/iu', 'Aprendiz Masón ', $text);
        $text = preg_replace('/\bAp\s*∴/iu', 'Aprendiz ', $text);
        $text = preg_replace('/\bA\s*∴\s*M\s*∴?/iu', 'Aprendiz Masón ', $text);

        $text = preg_replace('/\bCC\s*∴\s*MM\s*∴?/iu', 'Compañeros Masones ', $text);
        $text = preg_replace('/\bCComp\s*∴?/iu', 'Compañeros ', $text);
        $text = preg_replace('/\bComp\s*∴\s*M\s*∴?/iu', 'Compañero Masón ', $text);
        $text = preg_replace('/\bComp\s*∴/iu', 'Compañero ', $text);
        $text = preg_replace('/\bC\s*∴\s*M\s*∴?/iu', 'Compañero Masón ', $text);

        $text = preg_replace('/\bMM\s*∴\s*MM\s*∴?/iu', 'Maestros Masones ', $text);
        $text = preg_replace('/\bMM\s*∴/iu', 'Maestros ', $text);
        $text = preg_replace('/\bM\s*∴\s*M\s*∴?/iu', 'Maestro Masón ', $text);

        $text = preg_replace('/\b1er\s+Grado\b/iu', 'Primer Grado', $text);
        $text = preg_replace('/\b2do\s+Grado\b/iu', 'Segundo Grado', $text);
        $text = preg_replace('/\b3er\s+Grado\b/iu', 'Tercer Grado', $text);

        // 8. Términos Litúrgicos, Documentos y Lugares
        $text = preg_replace('/\bOr\s*∴\s*de\b/iu', 'Oriente de ', $text);
        $text = preg_replace('/\bOr\s*∴/iu', 'Oriente ', $text);
        $text = preg_replace('/\bVall?\s*∴/iu', 'Valle ', $text);
        $text = preg_replace('/\bC[aá]m\s*∴\s*del\s+Med\s*∴?/iu', 'Cámara del Medio ', $text);
        $text = preg_replace('/\bProff\s*∴/iu', 'Profanos ', $text);
        $text = preg_replace('/\bProf\s*∴/iu', 'Profano ', $text);
        $text = preg_replace('/\bPlanch\s*∴?/iu', 'Plancha ', $text);
        $text = preg_replace('/\bPlza\s*∴?/iu', 'Plancha ', $text);
        $text = preg_replace('/\bPl\s*∴/iu', 'Plancha ', $text);
        $text = preg_replace('/\bCuad\s*∴?/iu', 'Cuadro ', $text);
        $text = preg_replace('/\bColl\s*∴/iu', 'Columnas ', $text);
        $text = preg_replace('/\bCol\s*∴/iu', 'Columna ', $text);
        $text = preg_replace('/\bTron\s*∴/iu', 'Trono ', $text);
        $text = preg_replace('/\bP\s*∴\s*P\s*∴?/iu', 'Palabra de Pase ', $text);
        $text = preg_replace('/\bP\s*∴\s*S\s*∴?/iu', 'Palabra Sagrada ', $text);

        // 9. Reemplazo de deltas residuales no capturados por micro-pausa fónica suave (, )
        $text = preg_replace('/\s*∴\s*/u', ', ', $text);

        return $text;
    }

    /**
     * Expands and cleans general symbols, bullet points, Markdown tags and Spanish abbreviations.
     */
    public function expandGeneralSymbolsAndAbbreviations(string $text): string
    {
        // 1. Remove typographic bullets and noise markers
        $text = preg_replace('/^[ \t]*[•▪▫◦■□►▻★☆✓✔]+[ \t]*/mu', '', $text);
        $text = preg_replace('/[•▪▫◦■□►▻★☆✓✔]/u', ' ', $text);

        // 2. Remove footnote brackets like [1], [24]
        $text = preg_replace('/\[\d+\]/', '', $text);

        // 3. Remove long horizontal dividers (---, ___, ===)
        $text = preg_replace('/[-_─=]{3,}/u', ' ', $text);

        // 4. Clean Markdown emphasis (*bold*, _italic_)
        $text = preg_replace('/[*_]{1,3}([^*_]+)[*_]{1,3}/u', '$1', $text);

        // 5. Expand symbols
        $text = preg_replace('/(\d+)\s*%/u', '$1 por ciento', $text);
        $text = preg_replace('/\b&\b/u', 'y', $text);
        $text = preg_replace('/\s*±\s*/u', ' más o menos ', $text);
        $text = preg_replace('/\s*=\s*/u', ' igual a ', $text);
        $text = preg_replace('/(?<=\d)\s*€|€\s*(?=\d)/u', ' euros ', $text);
        $text = preg_replace('/(?<=\d)\s*\$|\$\s*(?=\d)/u', ' dólares ', $text);
        $text = preg_replace('/\s*@\s*/u', ' arroba ', $text);

        // 6. Ordinals
        $text = preg_replace('/\b1[º°]\b/u', 'primer', $text);
        $text = preg_replace('/\b2[º°]\b/u', 'segundo', $text);
        $text = preg_replace('/\b3[º°]\b/u', 'tercer', $text);
        $text = preg_replace('/\b4[º°]\b/u', 'cuarto', $text);
        $text = preg_replace('/\bN[º°]\.?\s*(\d+)/iu', 'número $1', $text);
        $text = preg_replace('/\bn[º°]\.?\s*(\d+)/iu', 'número $1', $text);

        // 7. Common Spanish abbreviations
        $text = preg_replace('/\bp[aá]g\.\s*(\d+)/iu', 'página $1', $text);
        $text = preg_replace('/\bp[aá]gs\.\s*(\d+)/iu', 'páginas $1', $text);
        $text = preg_replace('/\bcap\.\s*(\d+|[IVXLCDM]+)/iu', 'capítulo $1', $text);
        $text = preg_replace('/\bart\.\s*(\d+)/iu', 'artículo $1', $text);
        $text = preg_replace('/\bej\.\s*(?:g\.)?/iu', 'por ejemplo ', $text);
        $text = preg_replace('/\betc\./iu', 'etcétera', $text);
        $text = preg_replace('/\bDr\.\s+/u', 'Doctor ', $text);
        $text = preg_replace('/\bDra\.\s+/u', 'Doctora ', $text);
        $text = preg_replace('/\bSr\.\s+/u', 'Señor ', $text);
        $text = preg_replace('/\bSra\.\s+/u', 'Señora ', $text);
        $text = preg_replace('/\bIng\.\s+/u', 'Ingeniero ', $text);
        $text = preg_replace('/\bLic\.\s+/u', 'Licenciado ', $text);
        $text = preg_replace('/\bUd\.\b/u', 'Usted', $text);
        $text = preg_replace('/\bUds\.\b/u', 'Ustedes', $text);
        $text = preg_replace('/\bvol\.\s*(\d+)/iu', 'volumen $1', $text);
        $text = preg_replace('/\bn[uú]m\.\s*(\d+)/iu', 'número $1', $text);

        // 8. Long web URLs replaced with "enlace web"
        $text = preg_replace('/https?:\/\/\S+/iu', 'enlace web', $text);

        return $text;
    }

    /**
     * Bridges soft line-breaks inside sentences into continuous text with spaces,
     * maintaining natural audio pauses only where punctuation actually exists.
     */
    public function bridgeSentenceContinuity(string $text): string
    {
        // Fix hyphenated word breaks at end of line (e.g. transfor-\nmación -> transformación)
        $text = preg_replace('/([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)-\n([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)/u', '$1$2', $text);

        // Standardize multiple line breaks to max 2 (\n\n) for real paragraphs
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Bridge any single line-break (not preceded or followed by \n) into a single space
        $text = preg_replace('/(?<!\n)\n(?!\n)/u', ' ', $text);

        // Collapse duplicate horizontal spaces
        $text = preg_replace('/[ \t]+/', ' ', $text);

        return $text;
    }
}
