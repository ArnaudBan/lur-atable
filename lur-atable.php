<?php
/*
Plugin Name: LUR aTable
Plugin URI: http://atable.arnaudban.me
Description: Plugin WordPress qui permet de gérer nos repas en commun chez Eluère et associés
Version: 1.0
Author: ArnaudBan
Author URI: http://arnaudban.me
License: GPL2

Copyright 2013  ArnaudBan  (email : arnaud.banvillet@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Add a CPT
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-type-repas.php';