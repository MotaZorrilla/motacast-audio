<?php

namespace App\Services;

class TtsTextNormalizerService
{
    /**
     * Checks if the text exhibits Masonic / symbolic context.
     */
    public function isMasonicText(string $text): bool
    {
        // 1. Unicode delta (∴)
        if (preg_match('/[∴]/u', $text)) {
            return true;
        }

        // 2. Typical Masonic acronyms and formulas
        if (preg_match('/\b(GADU|S\.F\.U\.|T\.A\.F\.|A\.L\.|E\.V\.)\b/i', $text)) {
            return true;
        }

        // 3. Parenthesis-based Masonic abbreviations (Word symbol glyph mapping)
        if (preg_match('/\b(Ven|QQ?|HH?|VV?ig|VVisit|Orad|Secr|Tes|Hosp|Exp|Resp)\s*\(/i', $text)) {
            return true;
        }

        // 4. Dot-colon or colon-dot abbreviations (Q:. H:., V:. M:.)
        if (preg_match('/[A-ZÁÉÍÓÚÑ]{1,3}\s*[:.]{2,3}\s*[A-ZÁÉÍÓÚÑ]{1,3}\s*[:.]{2,3}/iu', $text)) {
            return true;
        }

        // 5. Explicit symbolic keywords
        if (preg_match('/\b(francmas[oó]n|mas[oó]n|masoner[ií]a|logia|taller|plancha|venerable\s+maestro|escuadra\s+y\s+comp[aá]s|gran\s+arquitecto|templo\s+mas[oó]nico|c[aá]mara\s+del\s+medio|tres\s+puntos)\b/iu', $text)) {
            return true;
        }

        return false;
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

        // Check if Masonic mode should be engaged
        $isMasonic = $forceMasonic ?? $this->isMasonicText($text);

        if ($isMasonic) {
            $text = $this->expandMasonicAbbreviations($text);
        }

        // General formatting and symbols cleanup
        $text = $this->expandGeneralSymbolsAndAbbreviations($text);

        // Sentence continuity and soft line breaks
        $text = $this->bridgeSentenceContinuity($text);

        return trim($text);
    }

    /**
     * Expands ritual and symbolic Masonic abbreviations into solemn, clear words.
     */
    public function expandMasonicAbbreviations(string $text): string
    {
        // 1. Invocation & Landmark formulas
        $text = preg_replace('/A\s*L\s*[\(\.:∴]\s*G\s*[\(\.:∴]\s*D\s*[\(\.:∴]\s*G\s*[\(\.:∴]\s*A\s*[\(\.:∴]\s*D\s*[\(\.:∴]\s*U\s*[\(\.:∴]?/iu', 'A la Gloria del Gran Arquitecto del Universo', $text);
        $text = preg_replace('/\b(?:G[\.:∴]\s*A[\.:∴]\s*D[\.:∴]\s*U[\.:∴]?|GADU)\b/iu', 'Gran Arquitecto del Universo', $text);
        $text = preg_replace('/\bS\s*[\(\.:∴]\s*F\s*[\(\.:∴]\s*U\s*[\(\.:∴]?/iu', 'Salud, Fuerza, Unión', $text);
        $text = preg_replace('/\bT\s*[\(\.:∴]\s*A\s*[\(\.:∴]\s*F\s*[\(\.:∴]?/iu', 'Triple Abrazo Fraternal', $text);
        $text = preg_replace('/\bL\s*[\(\.:∴]\s*I\s*[\(\.:∴]\s*F\s*[\(\.:∴]?/iu', 'Libertad, Igualdad, Fraternidad', $text);
        $text = preg_replace('/\bE\s*[\(\.:∴]\s*V\s*[\(\.:∴]?/iu', 'Era Vulgar', $text);
        $text = preg_replace('/\bA\s*[\(\.:∴]\s*L\s*[\(\.:∴]?\s*(\d{4})/iu', 'Año de la Verdadera Luz $1', $text);
        $text = preg_replace('/\bA\s*[\(\.:∴]\s*L\s*[\(\.:∴]?/iu', 'Año de la Verdadera Luz', $text);

        // 2. Venerables y Jerarquías
        $text = preg_replace('/\bEx\s*[-–]\s*Ven\s*[\(\.:∴]?\s*M\s*[\(\.:∴]?/iu', 'Ex Venerable Maestro ', $text);
        $text = preg_replace('/\bVen\s*[\(\.:∴]\s*M\s*[\(\.:∴]?/iu', 'Venerable Maestro ', $text);
        $text = preg_replace('/\bV\s*[\.:∴]\s*M\s*[\.:∴]?/iu', 'Venerable Maestro ', $text);
        $text = preg_replace('/\bMuy\s+Resp\s*[\(\.:∴]?\s*Gr\s*[\(\.:∴]?\s*M\s*[\(\.:∴]?/iu', 'Muy Respetable Gran Maestro ', $text);
        $text = preg_replace('/\bM\s*[\.:∴]\s*Resp\s*[\.:∴]?/iu', 'Muy Respetable ', $text);

        // 3. Logias y Talleres
        $text = preg_replace('/\bResp\s*[\(\.:∴]\s*Log\s*[\(\.:∴]\s*Simb\s*[\(\.:∴]?/iu', 'Respetable Logia Simbólica ', $text);
        $text = preg_replace('/\bR\s*[\.:∴]\s*L\s*[\.:∴]\s*S\s*[\.:∴]?/iu', 'Respetable Logia Simbólica ', $text);
        $text = preg_replace('/\bResp\s*[\(\.:∴]\s*Log\s*[\(\.:∴]?/iu', 'Respetable Logia ', $text);
        $text = preg_replace('/\bR\s*[\.:∴]\s*L\s*[\.:∴]?/iu', 'Respetable Logia ', $text);
        $text = preg_replace('/\bGr\s*[\(\.:∴]\s*Log\s*[\(\.:∴]?/iu', 'Gran Logia ', $text);
        $text = preg_replace('/\bG\s*[\.:∴]\s*L\s*[\.:∴]?/iu', 'Gran Logia ', $text);
        $text = preg_replace('/\bGr\s*[\(\.:∴]\s*Or\s*[\(\.:∴]?/iu', 'Gran Oriente ', $text);
        $text = preg_replace('/\bG\s*[\.:∴]\s*O\s*[\.:∴]?/iu', 'Gran Oriente ', $text);

        // 4. Hermanos y Saludos
        $text = preg_replace('/\bQQ\s*[\(\.:∴]\s*HH\s*[\(\.:∴]\s*Todos\b/iu', 'Queridos Hermanos Todos ', $text);
        $text = preg_replace('/\bQQ\s*[\(\.:∴]\s*HH\s*[\(\.:∴]\s*1[º°]\s+y\s+2[º°]\s+VVig\s*[\(\.:∴]?/iu', 'Queridos Hermanos Primer y Segundo Vigilantes ', $text);
        $text = preg_replace('/\bQQ\s*[\(\.:∴]\s*HH\s*[\(\.:∴]\s*VVisit\s*[\(\.:∴]?/iu', 'Queridos Hermanos Visitadores ', $text);
        $text = preg_replace('/\bQQ\s*[\(\.:∴]\s*HH\s*[\(\.:∴]?/iu', 'Queridos Hermanos ', $text);
        $text = preg_replace('/\bQ\s*[\(\.:∴]\s*H\s*[\(\.:∴]?/iu', 'Querido Hermano ', $text);
        $text = preg_replace('/\bHH\s*[\(\.:∴]/iu', 'Hermanos ', $text);
        $text = preg_replace('/\bH\s*[\(\.:∴]\s+(?=[A-ZÁÉÍÓÚÑ])/u', 'Hermano ', $text);

        // 5. Oficiales y Dignidades
        $text = preg_replace('/\b1[º°]\s+y\s+2[º°]\s+VVig\s*[\(\.:∴]?/iu', 'Primer y Segundo Vigilantes ', $text);
        $text = preg_replace('/\b1[º°]\s+Vig\s*[\(\.:∴]?/iu', 'Primer Vigilante ', $text);
        $text = preg_replace('/\b2[º°]\s+Vig\s*[\(\.:∴]?/iu', 'Segundo Vigilante ', $text);
        $text = preg_replace('/\bVVig[\(\.:∴]?(?![a-zA-ZáéíóúÁÉÍÓÚñÑ])/iu', 'Vigilantes ', $text);
        $text = preg_replace('/\bVig[\(\.:∴]?(?![a-zA-ZáéíóúÁÉÍÓÚñÑ])/iu', 'Vigilante ', $text);
        $text = preg_replace('/\bVVisit\s*[\(\.:∴]?/iu', 'Visitadores ', $text);
        $text = preg_replace('/\bOrad\s*[\(\.:∴]?/iu', 'Orador ', $text);
        $text = preg_replace('/\bSecr\s*[\(\.:∴]?/iu', 'Secretario ', $text);
        $text = preg_replace('/\bTes\s*[\(\.:∴]?/iu', 'Tesorero ', $text);
        $text = preg_replace('/\bHosp\s*[\(\.:∴]?/iu', 'Hospitalario ', $text);
        $text = preg_replace('/\bM\s*[\(\.:∴]\s*de\s+Cer\s*[\(\.:∴]?/iu', 'Maestro de Ceremonias ', $text);
        $text = preg_replace('/\bG\s*[\(\.:∴]\s*T\s*[\(\.:∴]\s*I\s*[\(\.:∴]?/iu', 'Guarda Templo Interior ', $text);
        $text = preg_replace('/\bG\s*[\(\.:∴]\s*T\s*[\(\.:∴]\s*E\s*[\(\.:∴]?/iu', 'Guarda Templo Exterior ', $text);
        $text = preg_replace('/\bExp\s*[\(\.:∴]?/iu', 'Experto ', $text);

        // 6. Grados
        $text = preg_replace('/\bA\s*[\.:∴]\s*M\s*[\.:∴]?/iu', 'Aprendiz Masón ', $text);
        $text = preg_replace('/\bAp\s*[\(\.:∴]\s*M\s*[\(\.:∴]?/iu', 'Aprendiz Masón ', $text);
        $text = preg_replace('/\bAp\s*[\.:∴]/iu', 'Aprendiz ', $text);
        $text = preg_replace('/\bAA\s*[\.:∴]\s*MM\s*[\.:∴]?/iu', 'Aprendices Masones ', $text);
        $text = preg_replace('/\bC\s*[\.:∴]\s*M\s*[\.:∴]?/iu', 'Compañero Masón ', $text);
        $text = preg_replace('/\bComp\s*[\(\.:∴]\s*M\s*[\(\.:∴]?/iu', 'Compañero Masón ', $text);
        $text = preg_replace('/\bComp\s*[\.:∴]/iu', 'Compañero ', $text);
        $text = preg_replace('/\bCC\s*[\.:∴]\s*MM\s*[\.:∴]?/iu', 'Compañeros Masones ', $text);
        $text = preg_replace('/\bM\s*[\(\.:∴]\s*M\s*[\(\.:∴]?/iu', 'Maestro Masón ', $text);
        $text = preg_replace('/\bMM\s*[\.:∴]\s*MM\s*[\.:∴]?/iu', 'Maestros Masones ', $text);

        // 7. Lugares y Cuerpos
        $text = preg_replace('/\bOr\s*[\(\.:∴]\s*de\b/iu', 'Oriente de ', $text);
        $text = preg_replace('/\bOr\s*[\(\.:∴]/iu', 'Oriente ', $text);
        $text = preg_replace('/\bVall?\s*[\(\.:∴]/iu', 'Valle ', $text);
        $text = preg_replace('/\bC[aá]m\s*[\(\.:∴]\s*del\s+Med\s*[\(\.:∴]?/iu', 'Cámara del Medio ', $text);
        $text = preg_replace('/\bProf\s*[\(\.:∴]/iu', 'Profano ', $text);
        $text = preg_replace('/\bProff\s*[\(\.:∴]/iu', 'Profanos ', $text);
        $text = preg_replace('/\bPlanch\s*[\(\.:∴]/iu', 'Plancha ', $text);
        $text = preg_replace('/\bPl\s*[\.:∴]/iu', 'Plancha ', $text);
        $text = preg_replace('/\bCol\s*[\.:∴]/iu', 'Columna ', $text);
        $text = preg_replace('/\bColl\s*[\.:∴]/iu', 'Columnas ', $text);
        $text = preg_replace('/\bTron\s*[\.:∴]/iu', 'Trono ', $text);

        // Limpiar deltas Unicode restantes (∴) reemplazándolos con una coma suave para micro-pausa
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
