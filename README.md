Doctor Login
     ↓
Session stores doctor_id
     ↓
Patient Form
     ↓
Patient saved in MySQL
     ↓
Patient ID added to URL
     ↓
calculator/index.php?patient_id=5
     ↓
Calculator verifies doctor + patient
     ↓
Loads patient information
     ↓
Doctor enters glucose + insulin
     ↓
PHP calculates HOMA-IR
     ↓
Result displayed

---

## Sign-up page & Google sign-up

`auth/signup.php` has a required **"I agree to the Terms & Conditions and Privacy
Policy"** checkbox (checked in the browser *and* on the server), and a
**Sign up with Google** button. Both the normal form and Google sign-up end at
`patients/add.php`, exactly like before.

### Google sign-up setup

1. Open <https://console.cloud.google.com/apis/credentials> and create an
   **OAuth client ID** of type **Web application**.
2. Under **Authorized redirect URIs** add the callback URL of your project, e.g.

   ```
   http://localhost/HOMA-IR%20Authentication/auth/google-callback.php
   ```

   (If Google rejects the `%20`, rename the project folder without a space, or
   set `GOOGLE_REDIRECT_URI` to the exact URL you registered. When you try the
   button on localhost before setup, the page shows the exact URI it expects.)
3. Create `config/google.local.php` (it is git-ignored):

   ```php
   <?php
   return [
       "client_id"     => "1234-abc.apps.googleusercontent.com",
       "client_secret" => "GOCSPX-xxxxxxxx",
   ];
   ```

   or set the `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` environment variables.
4. PHP needs the **cURL** extension. On XAMPP/Windows, if Google sign-up fails
   with a certificate error in the PHP error log, set `curl.cainfo` in `php.ini`
   to a CA bundle (<https://curl.se/docs/caextract.html>).

How it behaves: a doctor whose Google email already exists is simply signed in;
otherwise a new doctor account is created (with a random, unusable password).
Only emails that Google reports as verified are accepted.

The Terms & Conditions / Privacy Policy pages (`auth/terms.php`,
`auth/privacy.php`) contain starter text - replace it with your own.
