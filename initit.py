#!/usr/bin/python3


import os

dirs = ['temp', 'config']

for dr in dirs:
    if os.path.isdir(dr):
        os.chmod(dr, 0o777)

create_files = ['mailparser.log', 'mailparser_error.log', 'config/config.php']

for fl in create_files:
    if os.path.isfile(fl):
        file = open(fl, 'w+')
        file.write('')
        file.close()


config_file = open('config/config.php', 'w+')
config_data = '''<?php

return [
    'mta_connection' => 'mysql://DB_USER:DB_PASSWORD@localhost/mailserver',
    'group_prefix' => '-group',
    'installed' => false,
    'collab_user' => 'collab_user',
    'collab_user_password' => 'oi#u78)ws@pq19ed0',
    'mail_domain' => false,
    'server_host' => false,
    'site_url' => false,
];
'''

config_file.write(config_data)
config_file.close()