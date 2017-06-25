# v3s3-cakephp3
A simple storage system RESTful API written in YII 2

This is part of a collection of repositories which provides an implementation of a simple storage system RESTful API using different PHP Frameworks. The project aims to directly compare the differences in setup and syntax between each of the represented frameworks.

See the Wiki page for a hyperlinked list of all the repositories in the collection.

<hr />

# INSTALLATION AND IMPLEMENTATION SPECIFICS
```
cd path/to/htdocs
composer create-project --prefer-dist yiisoft/yii2-app-basic YII_ROOTDIR
```
(where YII_ROOTDIR is the preferred name of your YII root directory within htdocs)

The bower-asset/jquery dependency is required to install YII 2 using composer. In some cases composer must be updated and the fxp/composer-asset-plugin installed in order to resolve the YII 2 dependency issue.<br />
Steps to install:<br />
Rename the **/path/to/composer/vendor** directory to **/path/to/composer/vendor.old**
Rename the **/path/to/composer/composer.lock** file to  **/path/to/composer/composer.lock.old**
```
cd /path/to/composer
composer clear-cache
composer self-update
composer global require "fxp/composer-asset-plugin:~\<VERSION\>"
composer install
```
(where \<VERSION\> is the current release version number which you can obtain from [https://packagist.org/packages/fxp/composer-asset-plugin](https://packagist.org/packages/fxp/composer-asset-plugin))

YII 2 comes with a code generator called **GII** which we can use to create the **V3s3** module skeleton. The code generator can be accessed by opening the following URL:<br />
**\[SCHEME\://\]\<HOST\>\[:PORT\]/?r=gii**<br />
(where \[SCHEME\://\], \<HOST\> and \[:PORT\] should be replaced with the proper server URL components depending on your setup)<br />
(does not seem to work when the forward slash before the question mark is omitted)<br />
(if "pretty url" is enabled in the router configuration by setting `'enablePrettyUrl' => true` in the **path/to/YII_ROOTDIR/config/web.php** file the page can be accessed using **\[SCHEME\://\]\<HOST\>\[:PORT\]/gii**)

Once the page is loaded there will be a "Module Generator" section which can be accessed by clicking on the section's "Start" button. The next step requires filling in the fully qualified module class name and a module id which can be set to **\app\modules\V3s3\Module** and **V3s3** respectively. Clicking on "Preview" shows which files are going to be created by the utility. As the V3s3 module works with simple responses the view file will not be needed and should be skipped by unchecking the box. Clicking on the "Generate" button creates the selected module files. The generator also displays a reminder that the module must be included in the application configuration to be usable.<br />
There is also a model generator which needs to be run separately in order to generate the table's ActiveRecord model class. From the **GII** home page we follow the "Model Generator" link and are presented with the corresponding form. There we must fill in the proper table name, the Model Class (ex. V3s3Model), the namespace (ex. app\modules\V3s3\models) and optionally enable I18N with Message Category **V3s3**. Clicking on "Preview" and then "Generate" finalizes the task.

Notable new/modified project-specific files:<br />
**path/to/YII_ROOTDIR/modules/V3s3/Module.php** (GII Module Generator created and user edited)
**path/to/YII_ROOTDIR/modules/V3s3/controllers/DefaultController.php** (GII Module Generator created and user edited)
**path/to/YII_ROOTDIR/modules/V3s3/modules/V3s3Model.php** (GII Model Generator created and user edited)
**path/to/YII_ROOTDIR/config/.gitignore** (create file with the proper pattern)<br />
**path/to/YII_ROOTDIR/config/db.local.php** (user created; use database.local.php.dist for reference)<br />
**path/to/YII_ROOTDIR/config/web.php** (modify line 4 and add lines 50-62 and 65-71)