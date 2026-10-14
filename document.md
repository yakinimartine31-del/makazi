
# KILO AI MASTER PROMPT — AURA ACCOMMODATION RENTAL SYSTEM

## 1. Project Overview

Act as a senior Laravel developer, software architect, database designer, UI/UX designer, and application security engineer.

Build a production-oriented accommodation rental platform called **AURA** (Affordable Urban Relaxing Accommodations).

The platform will connect property owners with tenants looking for rooms to rent. Owners can upload their rooms, tenants can search and view room details, and tenants can contact owners to arrange property viewings and rental agreements.

The system owner earns income by charging property owners a **5% room listing fee**. Rooms created directly by the administrator are exempt from this fee.

The platform will eventually support multiple accommodation modules, including:

1. Rooms — implement this first.
2. Hostels — future module.
3. Apartments — future module.
4. Houses — future module.
5. Other accommodation types — future extensions.

**IMPORTANT: Start with the Rooms module only. Do not implement the other accommodation modules yet. However, design the architecture so that they can be added without rebuilding the entire application.**

## 2. Technology Stack

Use the following technologies:

- Backend: Laravel, using a stable version compatible with the installed PHP environment.
- Frontend: Laravel Blade, HTML5, CSS3, JavaScript and Bootstrap 5.
- Database: MySQL 8.
- Environment: Docker and Docker Compose.
- Authentication: Laravel authentication with secure password hashing.
- Authorization: Role-based access control and Laravel policies.
- Image storage: Laravel filesystem, with images stored persistently.
- Payments: Create a payment integration layer that can support an appropriate Tanzanian payment provider when credentials and configuration are available.
- Version control: Git.

Do not introduce React, Vue, Next.js, or a separate frontend framework. The website must use Laravel Blade.

Use environment variables for sensitive configuration. Never hardcode database passwords, payment credentials, API secrets or application keys.

## 3. Application Architecture

Create two main interfaces within the same Laravel application.

### A. Public Website — Frontend

Suggested URL: `/`

The public website must allow visitors to:

- View the home page.
- Search for available rooms.
- Filter rooms by location, monthly rent, room type and amenities.
- View room details and photographs.
- See the monthly rent, deposit, available facilities and rental rules.
- View the approximate property location.
- Register and log in.
- Contact property owners.
- Request room viewings.
- Report suspicious or misleading listings.

Visitors should be able to browse published room listings without logging in. Require authentication where necessary for submitting enquiries, managing listings or performing other protected actions.

### B. Property Owner Dashboard

Suggested URL: `/owner`

Registered owners must be able to:

- View dashboard statistics.
- Create room listings.
- Save incomplete listings as drafts.
- Edit their own listings.
- Upload multiple room photographs.
- Preview a listing before submission.
- See the calculated 5% listing fee.
- Pay the required listing fee.
- View payment status and receipts.
- Track listing approval status.
- View enquiries and viewing requests.
- Mark rooms as available, reserved or rented.
- Update room information and availability.
- View their listing history.

An owner must never be able to modify another owner's property by changing an ID in a URL or request.

### C. Administrator Dashboard

Suggested URL: `/admin`

The administrator must be able to:

- View total users, owners, rooms, published listings and pending approvals.
- View platform revenue and payment summaries.
- Create and manage room listings.
- Upload rooms directly without paying the 5% listing fee.
- Create a room on behalf of a registered owner.
- Review owner-submitted rooms.
- Approve or reject listings.
- Provide rejection reasons.
- Edit and remove inappropriate listings.
- Manage tenants and owners.
- Suspend users when necessary.
- Manage regions, districts, wards and areas.
- Manage room categories and amenities.
- Configure the 5% listing fee.
- View payment transactions and payment references.
- Review failed payments and eligible refunds.
- View audit logs of important administrative actions.

Only authorized administrators may access administrative routes and actions.

## 4. Business Rules — Listing Fees

This is a core requirement. Implement it through a dedicated `ListingFeeService`, not scattered calculations in controllers.

### Rule 1: Owner-created listings

When a registered property owner creates a room listing:

1. Validate the room information.
2. Save the room as a draft or pending-payment listing.
3. Calculate the listing fee.
4. Display the rent and calculated fee to the owner.
5. Require payment before the listing becomes eligible for publication.
6. Verify payment on the server.
7. Allow the listing to proceed to the required approval process after payment succeeds.

### Rule 2: Administrator-created listings

When an administrator creates a listing directly for AURA:

- Listing fee must be zero.
- Payment must not be required.
- Record the administrator who created the listing.
- Apply the same listing validation and moderation standards.
- Do not allow ordinary users to impersonate an administrator or bypass the fee.

### Rule 3: Administrator uploads on behalf of an owner

Distinguish between:

- An AURA-owned or administrator-created listing, which is fee-exempt.
- A listing created by an administrator on behalf of a registered property owner, which is subject to the owner's 5% fee.

Store the listing owner and the authenticated uploader separately.

For example:

- `owner_id`: the person who owns or manages the rental property.
- `created_by`: the authenticated user who created the listing.
- `created_by_role`: an auditable record of the creator's role, if appropriate.
- `fee_exempt`: whether the listing is exempt, set only by authorized server-side logic.

Never determine fee exemption from a user-submitted form field.

### Rule 4: Fee calculation

For the initial version, use 5% of one month's advertised rent as the fee basis, and make this basis configurable for future changes.

Formula:

`listing_fee = monthly_rent × 0.05`

Examples:

| Monthly rent | Listing fee |
| -----------: | ----------: |
|   TZS 50,000 |   TZS 2,500 |
|  TZS 100,000 |   TZS 5,000 |
|  TZS 150,000 |   TZS 7,500 |
|  TZS 200,000 |  TZS 10,000 |

This is a one-time listing fee for the initial implementation, not a monthly recurring fee.

Store the calculated amount on the listing or associated fee record. Changing the rent later must not silently change the amount of an existing payment obligation.

Use appropriate decimal precision for financial data. Never use floating-point arithmetic for financial calculations.

### Rule 5: Listing status

Implement explicit listing statuses, such as:

- `draft`
- `pending_payment`
- `pending_approval`
- `published`
- `rejected`
- `suspended`
- `rented`
- `unavailable`

Implement separate payment statuses:

- `unpaid`
- `pending`
- `paid`
- `failed`
- `refunded`
- `waived`

Do not confuse listing status with payment status.

A room uploaded by an owner must not become publicly visible merely because a payment request was initiated. Payment must be verified before publication, and any required admin approval must also be satisfied.

For an administrator-created fee-exempt listing, record the exemption and allow the listing to follow the normal moderation process.

## 5. Rooms Module — Detailed Requirements

Build a multi-step room listing form using Blade.

### Step 1: Basic Information

Fields:

- Room title.
- Room type.
- Description.
- Rental purpose.
- Availability status.

Examples of room types include single room, self-contained room, shared room and master room. Allow the administrator to manage the available room types.

### Step 2: Room Details

Fields:

- Room size, if known.
- Number of bedrooms, if applicable.
- Bathroom type.
- Bathroom sharing status.
- Kitchen availability.
- Indoor or outdoor bathroom.
- Other relevant room features.

### Step 3: Location

Fields:

- Region.
- District.
- Ward.
- Area or street.
- Nearby landmark.
- Optional map coordinates.

Use related location tables or a suitable structured location model. Region, district and ward selections must be validated against existing records.

### Step 4: Pricing

Fields:

- Monthly rent in TZS.
- Deposit amount.
- Payment frequency.
- Minimum rental period.
- Negotiable rent indicator.
- Additional disclosed charges.

Display a live fee estimate in the interface, but recalculate the fee on the server before saving.

Example:

Monthly rent: TZS 100,000

Listing fee: TZS 5,000

The interface must explain that the listing fee is separate from the rent and deposit.

### Step 5: Amenities and Utilities

Support configurable amenities such as:

- Water availability.
- Electricity.
- Wi-Fi.
- Private bathroom.
- Shared bathroom.
- Kitchen.
- Laundry facilities.
- Garbage collection.
- Other administrator-defined amenities.

Allow multiple amenities per room.

### Step 6: Security and Parking

Fields:

- Security guard.
- Gated property.
- CCTV availability.
- Secure door or lock.
- Parking availability.
- Parking type.
- Additional security information.

### Step 7: Furnishing and Rental Rules

Fields:

- Furnished, partially furnished or unfurnished.
- Pets policy.
- Visitor rules.
- Smoking policy.
- Occupancy restrictions.
- Other rental conditions.

### Step 8: Nearby Facilities

Allow owners to describe nearby facilities, such as:

- Public transport.
- Shops and markets.
- Hospitals and clinics.
- Schools and universities.
- Places of work.
- Other relevant services.

### Step 9: Images and Documents

Allow multiple room photographs.

Requirements:

- Validate file type and file size.
- Generate safe filenames.
- Store files using Laravel's filesystem.
- Restrict uploads to supported image formats.
- Allow the owner to select a cover image.
- Allow owners to remove and reorder their images.
- Prevent unauthorized access to private documents.
- Never trust a client-supplied file extension alone.

Support optional property verification documents with suitable privacy protections.

### Step 10: Availability and Viewing

Fields:

- Available from date.
- Preferred viewing times.
- Viewing instructions.
- Room availability status.

Allow tenants to submit viewing requests and allow owners to accept, reject or reschedule them.

### Step 11: Contact and Review

Fields:

- Contact name.
- Preferred contact method.
- Phone number.
- Optional WhatsApp contact.
- Listing preview.

Require owners to confirm that their listing details are accurate before submission.

## 6. Tenant Room Search

Build a functional search page with:

- Keyword search.
- Region, district and ward filters.
- Minimum and maximum monthly rent.
- Room type.
- Bathroom type.
- Furnishing status.
- Amenities.
- Availability.
- Sorting by newest, price ascending and price descending.
- Pagination.

Only display published, available and otherwise eligible listings.

Each room card should display:

- Cover image.
- Room title.
- Location.
- Monthly rent in TZS.
- Key amenities.
- Availability.
- Link to view details.

The room details page should display all public listing information, photographs, pricing, rental rules and owner contact options.

Protect private owner information and do not expose internal IDs, payment credentials or verification documents.

## 7. Database Design

Design normalized MySQL tables and Laravel migrations for at least:

- `users`
- `roles` or an equivalent secure role system
- `properties` or a suitable room listing table
- `room_types`
- `room_images`
- `amenities`
- `room_amenity`
- `regions`
- `districts`
- `wards`
- `listing_fees`
- `payments`
- `enquiries`
- `viewing_requests`
- `property_documents`, if required
- `settings`
- `audit_logs`

Choose clear relationships and foreign keys. Add indexes for commonly used search fields, listing statuses, owners, locations and payment references.

Avoid creating separate tables for future accommodation modules before their requirements are defined. Use a structure that allows future modules to reuse common functionality while keeping room-specific fields separate where appropriate.

Create migrations and seeders for development data. Do not rely on manually created production tables.

## 8. Payment Integration

Create a provider-independent payment service interface.

Payment requirements:

- Support integration with an appropriate payment provider operating in Tanzania when credentials are available.
- Keep provider credentials in environment variables.
- Create a pending payment record before initiating a transaction.
- Store a unique internal payment reference.
- Verify callbacks or webhooks on the server.
- Verify transaction references and amounts.
- Make callback processing idempotent.
- Prevent duplicate fee collection.
- Handle failed, pending, successful and refunded transactions.
- Record payment timestamps and audit information.
- Provide a clear receipt or transaction record.

Do not mark a payment as successful merely because the browser redirects to a success page.

If payment-provider credentials are not available, implement a sandbox or mock payment adapter for local development. Do not pretend that mock payments are real payments.

Do not collect or hold tenant rent or security deposits as part of this initial listing-fee implementation. Those flows can be considered in a future phase.

## 9. Security Requirements

Implement:

- Authentication and authorization.
- Role-based access control.
- Laravel policies for room ownership.
- CSRF protection.
- Input validation.
- Output escaping in Blade.
- Secure password hashing.
- Rate limiting for authentication and sensitive endpoints.
- Secure image and document uploads.
- Protection against IDOR vulnerabilities.
- Protection against mass assignment.
- Database transactions where appropriate.
- Server-side fee calculation.
- Server-side payment verification.
- Secure session and cookie configuration.
- Audit logging for important admin operations.

Never trust a submitted `role`, `owner_id`, `created_by`, `listing_fee`, `payment_status` or `fee_exempt` value without enforcing the appropriate server-side rules.

Do not commit `.env` files, credentials, private documents or payment secrets to Git.

## 10. UI/UX Requirements

Create a clean, modern, responsive accommodation rental website suitable for Tanzanian users.

Use:

- Bootstrap 5.
- Mobile-friendly layouts.
- Clear navigation.
- Search forms that work on mobile and desktop.
- Room cards with good image presentation.
- Accessible form labels.
- Inline validation errors.
- Success and error notifications.
- Consistent dashboard navigation.
- Clear currency formatting in TZS.
- Confirmation dialogs for destructive actions.

The public website should be simple for tenants to use. The owner dashboard should make uploading and managing rooms straightforward. The admin dashboard should prioritize operational visibility and payment tracking.

Do not use fake buttons or nonfunctional UI elements. Every visible action should have a working implementation or be clearly marked as unavailable until a later phase.

## 11. Docker Environment

Create a complete Docker development environment.

Include:

- `Dockerfile`
- `docker-compose.yml`
- `.dockerignore`
- `.env.example`
- MySQL 8 service.
- Laravel application service.
- Persistent MySQL volume.
- Persistent storage for uploaded files.
- Application port mapping.
- Health checks where appropriate.
- Database connection configuration.
- Clear setup instructions.

Use Docker service names for internal database connections rather than `localhost`.

Do not expose MySQL publicly unless explicitly needed. Do not place real passwords in source control.

Document exact commands for building and starting the project, installing dependencies, generating the application key, running migrations, seeding development data and viewing application logs.

Do not overwrite an existing project, database or `.env` file without first inspecting it and preserving important configuration.

## 12. Development Workflow

Work in phases. Complete each phase before proceeding.

### Phase 1: Project inspection and foundation

- Inspect the current directory and installed tools.
- Determine whether a Laravel project already exists.
- Check PHP, Composer, Docker and Docker Compose availability.
- Create or configure the AURA project.
- Configure Docker and MySQL.
- Verify the application starts successfully.
- Set up authentication and roles.
- Create base Blade layouts.

### Phase 2: Database and Rooms module foundation

- Create migrations and models.
- Define relationships.
- Add seeders.
- Implement room validation and policies.
- Implement image storage.
- Verify migrations run successfully.

### Phase 3: Owner room management

- Implement the multi-step room upload workflow.
- Add draft saving and editing.
- Add image management.
- Add fee calculation and payment status.
- Prevent owners from modifying other owners' listings.

### Phase 4: Listing fees and payment records

- Implement the 5% fee service.
- Implement the admin exemption rules.
- Implement payment records.
- Add the mock payment adapter if no real provider credentials are available.
- Test successful and failed payment handling.

### Phase 5: Admin dashboard

- Implement room moderation.
- Implement user management.
- Implement listing fee settings.
- Implement payment reports.
- Implement audit logs.

### Phase 6: Tenant website

- Implement room search and filters.
- Implement room detail pages.
- Implement owner contact and viewing requests.
- Implement pagination and responsive layouts.

### Phase 7: Testing and documentation

- Test authentication and authorization.
- Test listing fee calculations.
- Test administrator exemptions.
- Test payment callbacks and duplicate requests.
- Test room search and image uploads.
- Fix discovered errors.
- Document installation, configuration and usage.

## 13. Testing and Acceptance Criteria

The system is not complete until these scenarios work:

1. A visitor can search and view published rooms.
2. A property owner can register and log in.
3. An owner can create a draft room listing.
4. A room with monthly rent of TZS 100,000 produces a fee of TZS 5,000.
5. An owner cannot publish a fee-required listing before verified payment and any required approval.
6. An administrator can create a fee-exempt listing with a zero listing fee.
7. An administrator uploading on behalf of an owner does not automatically exempt that owner's listing.
8. A normal user cannot obtain free listings by submitting an administrator role or exemption field.
9. An owner cannot edit or delete another owner's room.
10. Failed payment attempts do not publish a fee-required listing.
11. Duplicate payment callbacks do not create duplicate successful payments.
12. An administrator can approve or reject a room and provide a reason.
13. Tenants can filter rooms by location and price.
14. Uploaded images remain available after a Docker container restart.
15. The application runs successfully from the documented Docker setup instructions.

Write automated tests for critical business rules, especially fee calculation, authorization and payment handling.

## 14. Instructions for Kilo AI

Follow these rules throughout implementation:

- Work directly in the current project workspace.
- Inspect existing files before creating or modifying them.
- Do not merely return code snippets or a project plan. Implement the application.
- Use maintainable Laravel conventions and clear naming.
- Keep controllers focused and move business logic into services where appropriate.
- Use migrations rather than manual database changes.
- Do not create the Hostels, Apartments or Houses modules yet.
- Do not skip payment verification or authorization because they are difficult.
- Do not claim tests pass unless you actually run them.
- Run appropriate tests and fix errors before declaring a phase complete.
- If a required credential is unavailable, implement a clearly identified mock adapter and document the configuration required for real integration.
- Keep a concise record of files created, commands executed, tests run, outstanding issues and the next phase.
- If the project is too large to complete in one pass, implement it incrementally without abandoning the current phase.

## Final Objective

Deliver a working AURA Laravel accommodation rental system with:

1. A public Blade website.
2. A property owner dashboard.
3. An administrator dashboard.
4. A complete Rooms module.
5. A configurable 5% owner listing fee.
6. Free administrator-created listings under the defined exemption rules.
7. MySQL persistence and Docker configuration.
8. Secure payment-record handling.
9. Room search, room details, image uploads, enquiries and viewing requests.
10. Tests and clear installation documentation.

**Start by inspecting the workspace and completing Phase 1. Report the actual results before proceeding to the next phase.**
