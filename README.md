# Career Form

A lightweight WordPress plugin that drops a ready-made job application form onto your site — with a manageable field list, an applications inbox, and automatic email notifications.

## Features

- **Instant Career page** — on activation, the plugin creates a page (slug: `career`) with the `[career_form]` shortcode already in place. No page building required.
- **Manage Form Fields** — seven fields ship out of the box:
  - Locked (always required, always on): **Name, Email, Contact Number**
  - Editable (toggle visibility, rename, or make optional): **Qualification, Designation, Experience, Resume**
  - Drag to reorder fields on the live form.
- **Add New Field** — add custom fields (label, type, required, show-on-form) without touching code.
- **Applications table** — every submission lands in one sortable table (Name, Email, Qualification, Contact, Designation, Experience, Resume, Date). View or download the resume, delete single or multiple entries.
- **Applicant Email** — an optional, automatic thank-you email sent to the applicant on submission. Customize the from address/name, subject, title, and body (rich text), with `{name}` and `{designation}` placeholders.
- **Admin Copy Email** — an optional, separate notification sent to one or more HR/admin addresses whenever a new application comes in, with `{name}`, `{designation}`, and `{resume}` placeholders and its own content editor.
- **Mail delivery** — both emails are sent through WordPress's built-in `wp_mail()`. There's no SMTP configuration inside this plugin, so it works out of the box wherever `wp_mail()` already works. If your host blocks PHP's mail function, pair Career Form with any general-purpose SMTP plugin — no changes needed on this end.
- **Failure logging** — failed send attempts are logged to `wp-content/career-mail-error.log` for troubleshooting.

## Installation

1. Download or clone this repository into `wp-content/plugins/career-form`.
2. Activate **Career Form** from the WordPress admin **Plugins** screen.
3. A **Career** page is created automatically with the `[career_form]` shortcode. Add it to your menu, or place the shortcode on any page/post you like.
4. Go to **Career Settings** in the admin sidebar to manage fields, review applications, and configure email notifications.

## Folder structure

```
career-form/
├── career-form.php          # Plugin bootstrap
├── uninstall.php             # Cleanup on uninstall
├── admin/                    # Admin screens (fields, applications, mail settings) + assets
├── public/                   # Frontend shortcode assets
└── includes/
    ├── activation.php        # Creates the Career page on activation
    ├── fields.php             # Default + custom field definitions
    ├── shortcode.php          # [career_form] shortcode rendering
    ├── handlers.php           # Form submission handling
    └── mail-settings.php      # Applicant & admin email settings + wp_mail sending
```

## Documentation

See the [[`[docs/`](./docs](https://sharesofttech.github.io/Career-Form-Wp-Plugin/))](https://sharesofttech.github.io/Career-Form-Wp-Plugin/) folder (or open `[docs/index.html](https://sharesofttech.github.io/Career-Form-Wp-Plugin/)` in a browser) for a full walkthrough of every admin screen with screenshots.

