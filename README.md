# SessionCookieFix

Kanboard plugin that fixes broken forms after upgrading from Kanboard 1.2.54 or older to 1.2.55 or later, when Kanboard is installed in a subdirectory such as `https://example.com/kanboard/`.

## The problem

Kanboard 1.2.55 changed the path of the `KB_SID` session cookie from `/kanboard/` to `/kanboard`, but it never removes the cookie set by older versions. Browsers that logged in before the upgrade keep both cookies and send the old one first, so Kanboard reads a stale session id:

- every request starts a new session, while the user still looks logged in through the remember-me cookie;
- every form fails the CSRF check, for example "The title is required" when creating a task;
- logging out and back in does not help.

## What the plugin does

When a request carries both cookies, the plugin expires the old `/kanboard/` cookie and uses the current one for that request. Plugins are loaded before the session starts, so the first request is already fixed. Requests with a single cookie are not touched, and the plugin makes no database changes.

## Installation

Clone or extract the plugin into the `plugins` directory. The directory must be named `SessionCookieFix`:

```bash
cd /path/to/kanboard/plugins
git clone https://github.com/BerwangerDigital/bw-kanboard-session-cookie-fix.git SessionCookieFix
```

## Removal

Once a Kanboard release expires the old cookie by itself, delete the `plugins/SessionCookieFix` directory. Keeping the plugin alongside such a release is harmless.

## Compatibility

- Kanboard >= 1.2.55
- Any database driver
