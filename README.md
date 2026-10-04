# Skillrise Technologies — Industrial Training website

Live site: https://skillrisetechnologies.com (Hostinger WordPress).

The whole site is one WordPress plugin: `wp-content/plugins/industrial-training`.
Hostinger runs WordPress itself; only this plugin is uploaded.

## Folder

```
.
├── README.md                              # this file
├── dist/industrial-training.zip           # ready-to-upload plugin (not in git)
└── wp-content/plugins/industrial-training # the plugin source — see its README.md
```

## Deploy / update on Hostinger

1. Make the zip: right-click `wp-content/plugins/industrial-training` → **Send to → Compressed (zipped) folder**.
   The zip must contain the `industrial-training` folder at its top level. Upload it as-is — don't zip it twice
   (a file named `.zip.zip` won't install).
2. WP Admin → **Plugins → Add New → Upload Plugin** → choose the zip → **Install Now** →
   **Replace current with uploaded** (first time: **Activate**).
3. hPanel → your website → **Performance / CDN → Flush cache**, then reload the site with **Ctrl + F5**
   (or open it in an incognito window).

Settings, registrations and content you typed in WP Admin are kept when you replace the plugin.

## Where things are

| What | Where |
|---|---|
| Page text, stipend, contact, emails | WP Admin → **Settings → Industrial Training** |
| Registrations (search, CSV export) | WP Admin → **Registrations** |
| Founder, students, colleges | `wp-content/plugins/industrial-training/content/data.php` |
| Styles / animations | `wp-content/plugins/industrial-training/assets/css/itp.css`, `assets/js/itp.js` |

## Emails

Registration alerts use WordPress `wp_mail()`. On Hostinger install **WP Mail SMTP** with
`smtp.hostinger.com`, port 465 (SSL), and the `info@skillrisetechnologies.com` mailbox.
