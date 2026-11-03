---
paths:
  - "tests/**"
---

# Testing Conventions & Quality Floor

- **Testing Trophy Priority:** Focus test investment on comprehensive Feature Tests (`tests/Feature`) simulating authentic HTTP lifecycle, authorization policies, and database transactions.
- **Unhappy Paths:** Every feature test suite must verify validation rejections, unauthorized access attempts, and resource boundary isolation.
- **Zero Mock Fallbacks:** Use real database fixtures and file representations for reproducible, green test runs.
