<VirtualHost *:80>
    DocumentRoot "/var/bluemail"
    <Directory /var/bluemail>
        AllowOverride all
        Options Indexes FollowSymLinks ExecCGI
        AddHandler cgi-script .cgi .pl
        Order Deny,Allow
        $P{ALL}
    </Directory>
</VirtualHost>