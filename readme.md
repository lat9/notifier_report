# Zen Cart Notifier Report

As more and more plugins are relying on Zen Cart notifiers to perform their customizations, it can be a developer's debug nightmare if an expected notification isn't present.  It's kind of like you're waiting for something to do, but no one's there to say "Go do it".

This simple script traverses a site's `DIR_FS_CATALOG` directory ... and _all_ contained sub-directories ... searching all `.php` files for the presence of a `->notify` call (i.e. the notification itself).  Its output is created in a file named `notifier_report_YYYYMMDD_HHMMSS.txt` in the site's `/logs` directory.  That file contains a list of all `.php` files containing a notification, identifying the line number(s) and notifications issued.

Here's a sample of the output:
```
Start notifier report (v1.0.0), created 2019-09-16 13:54:54

C:/xampp/htdocs/testsite\admin\admin_activity.php
		line#293: $zco_notifier->notify('NOTIFY_ADMIN_ACTIVITY_LOG_RESET');

C:/xampp/htdocs/testsite\admin\attributes_controller.php
		line#421: $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_ADD_PRODUCT_ATTRIBUTES', $products_attributes_id);
		line#565: $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_UPDATE_PRODUCT_ATTRIBUTE', $attribute_id);
		line#584: $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ATTRIBUTE', array('attribute_id' => $attribute_id), $attribute_id);
		line#601: $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ALL', array('pID' => $_POST['products_filter']));
		line#615: $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_OPTION_NAME_VALUES', array('pID' => $_POST['products_filter'], 'options_id' => $_POST['products_options_id_all']));
```

**Note**: This is a _developer tool_, not a "proper" plugin.  There's no language file, no means (although it's open source) to create an entry in the admin's menu.

## Installation

Installation is simple (there's only one file).  Simply copy `notifier_report.php` to your site's admin directory (where the `admin_account.php` and `admin_activity.php` files reside).  Admin _superusers only_ will be able to access the script, entering a link similar to `www.example.com/myadmin/notifier_report.php` in the browser's address.  The script will take a while (depending on how many files are in that directory-tree) and will print a message to the screen once complete:
```
Report created: C:/xampp/htdocs/testsite/logs/notifier_report_20190916_135949.txt.
```

