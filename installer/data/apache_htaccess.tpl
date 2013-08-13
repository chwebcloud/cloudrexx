    # This is ASCMS_PATH_OFFSET, i.e.
    RewriteBase   /trunk2

    # Resolve language specific sitemap.xml
    RewriteRule ^(\w+)\/sitemap.xml$ sitemap_$1.xml [L,NC]

    # Allow directory index files
    RewriteCond %{REQUEST_FILENAME}/index.php -f
    RewriteRule   .  %{REQUEST_URI}/index.php [L,QSA]

    # Redirect all requests to non-existing files to Contrexx
    RewriteCond   %{REQUEST_FILENAME}  !-f
    RewriteRule   .  index.php?__cap=%{REQUEST_URI} [L,QSA]

    # Add captured request to index files
    RewriteRule ^(.*)index.php $1index.php?__cap=%{REQUEST_URI} [L,QSA]