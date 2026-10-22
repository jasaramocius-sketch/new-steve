# FAQ & Troubleshooting

Q: Homepage shows default content, not admin content.
A: Ensure home_page_sections table has active records and that migrations/seeders ran. Check HomeController and that `is_active` is true.

Q: Images not uploading or showing.
A: Verify `public/assets/images/` exists, correct permissions (www-data), and allowable file sizes/formats (<=5MB, jpg/png/gif/webp).

Q: Product import fails.
A: Use the sample CSV `sample-products-import.csv` as a template. Check for required headers and encoding (UTF-8). Review server PHP limits (upload_max_filesize, post_max_size).

Q: Queues failing or not processing.
A: Ensure supervisor or a queue worker is running. Check `php artisan queue:work` and monitor storage/logs/ for exceptions.

Q: How to reset local DB quickly?
A: Run `php artisan migrate:fresh --seed` (WARNING: destroys data). Use only on local/dev environments.

Q: How to create an admin user?
A: Use a seeder or Laravel Tinker:

  php artisan tinker
  \App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('secret'),'role'=>'master_admin']);

Q: Where to find the admin homepage manager guide?
A: See HOME_PAGE_ADMIN_GUIDE.md at the repo root (detailed instructions and schema).

If you encounter other issues, open an Issue with reproduction steps and environment details.