# Stockly — IDB Bali Campus Borrowing System

Stockly is a simple local PHP and MySQL project for borrowing campus facilities and equipment at Institut Desain dan Bisnis Bali (IDB Bali). It is designed as a small student assignment app: easy to understand, easy to run locally, and focused on practical borrowing workflows instead of external services or complex frameworks.

## Project purpose

This app supports a simple campus borrowing workflow for:

- Managing borrower records
- Managing facilities such as rooms, labs, and presentation spaces
- Managing goods and equipment inventory
- Creating borrowing requests with dates, purpose, and notes
- Approving, rejecting, checking out, and returning requests
- Preventing double-booking and overselling stock

The interface is in English and follows the existing AdminLTE/Bootstrap project structure while keeping the code manageable for a student project.

## Local requirements

- PHP 8.0 or newer
- MySQL 5.7 or newer
- A local web server such as XAMPP, Laragon, or MAMP
- A browser for local testing

## Installation and setup

1. Place the project in your local web root, for example:
   - `C:/xampp/htdocs/phpsimplecrud`
   - `D:/laragon/www/phpsimplecrud`
2. Start Apache and MySQL in your local server environment.
3. Create a local MySQL database called `db_simplecrud` if it does not already exist.
4. Import `db_simplecrud.sql` into MySQL using phpMyAdmin, HeidiSQL, MySQL Workbench, or the CLI.
5. Check the database settings in `config/db-config.php` and adjust the host, username, password, or database name if needed.
6. Open the app in your browser:
   - `http://localhost/phpsimplecrud/`

## Demo login accounts

The SQL import includes demo accounts for both required roles.

- User account
  - Email: `student@idbbali.ac.id`
  - Password: `password123`

- Operator account
  - Email: `operator@idbbali.ac.id`
  - Password: `operator123`

The User role can browse catalog items, check availability, submit requests, and view their own borrowing history. The Operator role can manage borrowers, facilities, goods, and request statuses.

## Role workflow

### User workflow

1. Sign in with the demo student account.
2. Open Browse & Availability to review goods and facility availability for a specific time range.
3. Choose a facility or equipment item, set the start and end date-times, and submit a borrowing request.
4. The request is created as Pending and is shown in My Requests.
5. Users can only view requests tied to their own borrower record and cannot change the status.

### Operator workflow

1. Sign in with the operator account.
2. Manage borrower records, facilities, and goods in the main operator pages.
3. Review all requests from the Loan Requests page.
4. Approve or reject pending requests only through valid workflow actions.
5. Record checkout and return events and update the status as needed.
6. Check dashboard metrics for pending requests, active bookings, overdue goods loans, and today’s activity.

## Availability logic

Availability is calculated from active reservations rather than permanently subtracting stock.

- Goods show total quantity and the quantity already reserved for the selected time range.
- A user may not request more than the remaining available quantity.
- Facilities are considered unavailable when an approved or checked-out booking overlaps the requested time range.
- Pending requests do not reduce availability.
- Approved and checked-out requests reserve the item or facility for the exact date-time range.
- Maintenance status blocks borrowing requests until the item is marked available again.
- Borrowing requests use full date-time start and end values, not date-only fields.

This keeps the logic understandable for a local student project while still allowing a practical campus borrowing flow.

## IDB Bali visual theme

The app keeps the IDB Bali identity through the institution logo in the header and a restrained campus-oriented color palette. The design uses a clean, readable dashboard style with subtle creative-industry cues rather than generic tourist imagery or unrelated decorative motifs.

The project uses the official IDB Bali logo when available and keeps it intact without distortion. It is used as a campus reference for the student project theme and not as an official institutional system.

## Sample data

The SQL import includes demo borrowers, facilities, equipment, and sample borrowing records based on realistic IDB Bali-style examples. These are sample records for local demo use only and can be edited freely.

## Notes and limitations

- This is a local project intended for learning and demonstration.
- It does not connect to external campus systems or external APIs.
- The app is intentionally simple and keeps the code readable for a PHP + MySQL assignment.
- Delete actions include confirmation prompts to reduce accidental data removal.

## Database configuration

The database settings live in:

- `config/db-config.php`

The default connection is meant for a local MySQL environment:

- Host: `localhost`
- User: `root`
- Password: empty
- Database: `db_simplecrud`

Update the values if your local environment uses different credentials.
