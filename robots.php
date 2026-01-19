<?php
header("Content-Type: text/plain");

// Get base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'];

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /dashboard/\n";
echo "Disallow: /login/\n";
echo "Disallow: /logout.php\n";
echo "Disallow: /register.php\n";
echo "Disallow: /process.php\n";
echo "Disallow: /404.php\n";
echo "Allow: /blog/\n";
echo "Allow: /category/\n";
echo "Allow: /tags/\n";
echo "Allow: /search/\n";
echo "\n";
echo "Sitemap: {$baseUrl}/sitemap.php\n";
