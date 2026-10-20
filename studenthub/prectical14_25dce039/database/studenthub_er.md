# StudentHub — Entity-Relationship (ER) Diagram

**Course:** ITUE203 — Web Development Frameworks  
**Practical:** 8, 9 & 10 — MySQL Schema Design, ER Model, Database Connectivity, Secure Registration & Authentication  
**Database:** `studenthub_db`

---

## ER Diagram (Mermaid Source)

```mermaid
erDiagram
    STUDENTS ||--o{ REGISTRATIONS : "registers for"
    EVENTS ||--o{ REGISTRATIONS : "has"
    USERS ||--o{ STUDENTS : "may link to" 

    STUDENTS {
        INT_UNSIGNED student_id PK "Auto-increment"
        VARCHAR(100) full_name NOT NULL
        VARCHAR(150) email NOT NULL UK
        VARCHAR(15) mobile NULL
        VARCHAR(100) course NOT NULL
        TINYINT_UNSIGNED year NOT NULL "1-4"
        VARCHAR(20) roll_no UK "e.g., 25DCE039"
        VARCHAR(50) department NULL
        DECIMAL(3,2) cgpa NULL "0.00-10.00"
        VARCHAR(50) city NULL
        TIMESTAMP created_at NOT NULL DEFAULT CURRENT_TIMESTAMP
    }

    EVENTS {
        INT_UNSIGNED event_id PK "Auto-increment"
        VARCHAR(200) title NOT NULL
        TEXT description NULL
        DATE event_date NOT NULL
        VARCHAR(200) location NOT NULL
        VARCHAR(50) category NULL "Technical, Cultural, Sports, Workshop"
        TIMESTAMP created_at NOT NULL DEFAULT CURRENT_TIMESTAMP
    }

    REGISTRATIONS {
        INT_UNSIGNED registration_id PK "Auto-increment"
        INT_UNSIGNED student_id FK NOT NULL
        INT_UNSIGNED event_id FK NOT NULL
        TIMESTAMP registration_date NOT NULL DEFAULT CURRENT_TIMESTAMP
        ENUM status NOT NULL DEFAULT 'registered' "registered, attended, cancelled"
    }

    USERS {
        INT_UNSIGNED user_id PK "Auto-increment"
        VARCHAR(50) username NOT NULL UK
        VARCHAR(150) email NOT NULL UK
        VARCHAR(255) password_hash NOT NULL "bcrypt from password_hash()"
        VARCHAR(100) full_name NOT NULL
        VARCHAR(15) phone NULL
        ENUM gender NULL "Male, Female, Other"
        VARCHAR(100) course NULL
        TINYINT_UNSIGNED year NULL "1-4"
        ENUM role NOT NULL DEFAULT 'student' "student, admin"
        TINYINT is_active NOT NULL DEFAULT 1
        TIMESTAMP last_login NULL
        VARCHAR(255) remember_token_hash NULL "SHA-256 hash of token"
        TIMESTAMP remember_token_expires NULL "Token expiry"
        TIMESTAMP created_at NOT NULL DEFAULT CURRENT_TIMESTAMP
        TIMESTAMP updated_at NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    }
```

---

## Relationships Explained

| Relationship | Type | Description |
|--------------|------|-------------|
| **Students → Registrations** | One-to-Many | One student can register for many events. Each registration belongs to exactly one student. |
| **Events → Registrations** | One-to-Many | One event can have many student registrations. Each registration is for exactly one event. |
| **Students ↔ Events** | Many-to-Many (via Registrations) | Students and events are connected through the `registrations` junction table. |
| **Users ↔ Students** | Optional One-to-One | A user account *may* link to a student profile (future enhancement). Currently separate for Practical 9. |

> **Note:** The `users` table (Practical 9) is for **authentication only**. The `students` table (Practical 8) stores **academic profiles**. They are intentionally separate — a user can exist without a student profile, and vice versa. Future practicals may link them.

---

## Key Constraints

### Primary Keys (PK)
- `students.student_id` — Unique identifier for each student
- `events.event_id` — Unique identifier for each event
- `registrations.registration_id` — Unique identifier for each registration record
- `users.user_id` — Unique identifier for each user account

### Foreign Keys (FK)
- `registrations.student_id` → `students.student_id`
  - `ON UPDATE CASCADE` — If student_id changes, update in registrations
  - `ON DELETE CASCADE` — If student deleted, their registrations are removed
- `registrations.event_id` → `events.event_id`
  - `ON UPDATE CASCADE` — If event_id changes, update in registrations
  - `ON DELETE CASCADE` — If event deleted, its registrations are removed

### Unique Constraints (UK)
- `students.email` — No two students can have the same email
- `students.roll_no` — No two students can have the same roll number
- `registrations (student_id, event_id)` — Prevents duplicate registrations for same student-event pair
- `users.username` — No two users can have the same username
- `users.email` — No two users can have the same email

### Indexes (for query performance)
- `students.course`, `students.department`, `students.year`
- `events.event_date`, `events.category`
- `registrations.student_id`, `registrations.event_id`, `registrations.status`
- `users.role`, `users.is_active`
- `users.remember_token_hash` — For remember-me token lookup

---

## Cardinality Summary

```
STUDENTS (1) ──────< (M) REGISTRATIONS (M) >────── (1) EVENTS

USERS (1) ──────< (0..1) STUDENTS (optional future link)
```

- **Student → Registrations**: 1 : N (One student, many registrations)
- **Event → Registrations**: 1 : N (One event, many registrations)
- **Student ↔ Event**: M : N (Resolved via Registrations junction table)
- **User ↔ Student**: 1 : 0..1 (Optional future linkage)

---

## How to View This Diagram

### Option 1: VS Code / GitHub / Markdown Preview
- Open `database/studenthub_er.md` in VS Code with Markdown preview (Ctrl+Shift+V)
- GitHub and GitLab render Mermaid diagrams automatically

### Option 2: Mermaid Live Editor
1. Go to https://mermaid.live/
2. Copy the Mermaid code block above (between ```mermaid and ```)
3. Paste into the editor to see interactive diagram

### Option 3: Export as Image
1. Use Mermaid Live Editor or VS Code extension "Markdown Preview Mermaid Support"
2. Export as PNG/SVG for lab report submission

---

## Schema-Diagram Consistency Checklist

- [x] All four tables shown: `students`, `events`, `registrations`, `users`
- [x] All columns match `studenthub_db.sql` exactly
- [x] Primary keys marked (PK)
- [x] Foreign keys marked (FK) with correct references
- [x] Unique constraints shown (UK)
- [x] Relationship cardinalities shown (1-to-Many, Many-to-Many, Optional)
- [x] Data types and constraints match SQL (NOT NULL, ENUM, DEFAULT)
- [x] Cascade behaviors documented

---

*Generated for Practical 8, 9 & 10 submission — StudentHub Project*