---
paths:
  - "app/Actions/**"
  - "app/Services/**"
  - "app/Http/Controllers/**"
  - "app/Http/Requests/**"
---

# Architecture & Design Conventions

- **Action Classes:** Use single-purpose Action classes in `app/Actions` with a public `handle()` method for write operations.
- **Domain Services:** Place external integration, audio streaming, and NLP logic in `app/Services`. Keep controllers thin.
- **Validation:** Always use dedicated Form Request classes in `app/Http/Requests` for incoming HTTP payload validation. Do not validate inline in controllers.
- **Authorization:** Use Eloquent Policies in `app/Policies` to protect book models and administrative actions.
- **Enums:** Use PHP 8.2+ backed enums in `app/Enums` (e.g. `BookStatus`, `ChapterStatus`) for model states.
