#!/usr/bin/python3


import os
import shutil


dirs = ['temp', 'config']
app_root = os.getcwd()


if app_root.split('/')[-1] != 'owncollab_talks':
    print("Error: owncollab_talks/config/config.php not exist in current directory")
    exit()


if not os.path.isdir(app_root + '/config'):
    os.mkdir(app_root + '/config', 0o777)
else:
    os.chmod(app_root + '/config', 0o777)


if os.path.isdir('temp'):
    shutil.rmtree(app_root + '/temp')

os.mkdir(app_root + '/temp', 0o777)

create_files = [
    'mailparser.log',
    'mailparser_error.log',
    'config/config.php'
]


# clean or create d files
for fl in create_files:
    file = open(app_root + '/' + fl, 'w+')
    file.write('')
    file.close()


config_file = open(app_root + '/config/config.php', 'w+')
config_data = '''<?php

return [
    'mta_connection' => false,
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

print("Script executed")