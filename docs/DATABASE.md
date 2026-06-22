# Database Schema

| Table | Purpose | Important Relationships |
|---|---|---|
| `users` | CMS users and account state | roles, permissions, authored records |
| `pages` | Static pages with publishing and SEO | creator/updater users |
| `posts` | Articles with publishing and SEO | creator/updater, categories, tags |
| `categories` | Hierarchical post taxonomy | self-referencing parent, posts |
| `tags` | Flat post taxonomy | posts |
| `category_post` | Post/category pivot | posts, categories |
| `post_tag` | Post/tag pivot | posts, tags |
| `media` | Local or S3 file metadata | uploader user |
| `menus` | Named navigation areas | menu items |
| `menu_items` | Nested, ordered links | menu, self-referencing parent |
| `settings` | Typed key/value configuration | updater user |
| `contact_messages` | Public contact submissions | reader user |
| `activity_logs` | Immutable audit trail | actor and polymorphic subject |
| `notifications` | Laravel database notifications | polymorphic notifiable |

Publishable records contain `meta_title`, `meta_description`, `og_title`,
`og_description`, `og_image`, and `canonical_url`. Foreign keys use restrictive or
nulling deletes to preserve auditability and avoid accidental content loss.

