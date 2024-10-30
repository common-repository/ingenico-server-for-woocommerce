=== Ingenico Server Integration Plugin ===
Contributors: BigDotSoftware
Donate link: 
Tags: woocommerce, invoice, ingenico, terminal, payment, print, vat, tax, fiscal, invoices, polcard, eservice, first data
Requires at least: 4.0
Requires PHP: 5.2.4
Tested up to: 5.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Ingenico terminals integration plugin | Plugin umożliwiający integrację z terminalami płatniczymi Ingenico

== Description ==
Plugin demonstrates a way to integrate Ingenico terminals with your WordPress/WooCommerce website. Ingenico fiscal terminals are widely used by eService, Polcard First Data and others in Poland.
Wtyczka pozwala na integrację terminali Ingenico z witryną WordPress/WooCommerce. Terminale płatnicze Ingenico są powszechnie stosowane przez eService, Polcard First Data oraz innych providerów w Polcse.

#### How does it work?
JavaScript cannot communicate with terminal directly, so it needs middle layer: Ingenico Server RESTful service (download it from here: https://blog.bigdotsoftware.pl/ingenico-server-instalacja/). WordPress Plugin generates JavaScript to perform HTTP/HTTPS requests to the Ingenico Server (as default running on localhost on ports 3020 and 3021, see more details here: https://blog.bigdotsoftware.pl/ingenico-server-wprowadzenie/).

JavaScript nie może komunikować się bezpośrednio z drukarką fiskalną, dlatego potrzebuje warstwy pośredniej: serwisu RESTful Ingenico Server (do pobrania: https://blog.bigdotsoftware.pl/ingenico-server-instalacja/). Wtyczka WordPress generuje JavaScript wykonujący requesty HTTP/HTTPS do Ingenico Server (domyślnie Ingenico Server działa na localhost na portach 3020 i 3021, więcej tutaj: https://blog.bigdotsoftware.pl/ingenico-server-wprowadzenie/).

#### Security note
We recommend communication between web browser and Ingenico Server service to be done via localhost (web browser like Chrome, Firefox, Safari etc. should run on the same computer where Ingenico Server is installed). Customizable/remote connections should be established via HTTPS, not HTTP. Wrong plugin configuration may cause data leaks and can be a subject of legal consequences. BigDotSoftware is not responsible for wrong plugin configuration and all consequences related to this. With encourage to contact us with any questions via email bigdotsoftware@bigdotsoftware.pl

Zalecamy aby przeglądarka www komunikowała się z serwisem Ingenico Server po localhost (przeglądarka www, taka jak Chrome, Firefox, Safari itp., powinna być uruchomiona na tym samym komputerze, na którym zainstalowany jest Ingenico Server). Połączenia zdalne powinny być wykonywane poprzez HTTPS, a nie HTTP. Nieprawidłowa konfiguracja wtyczki może powodować wycieki danych i może być przyczyną konsekwencji prawnych. BigDotSoftware nie ponosi odpowiedzialności za niewłaściwą konfigurację wtyczki oraz wszelkie związane z tym konsekwencje. Zachęcamy do kontaktu z nami za pośrednictwem poczty elektronicznej bigdotsoftware@bigdotsoftware.pl

#### Ingenico Server
https://bigdotsoftware.pl/ingenicoserver-restful-service-dla-terminali-platniczych/

#### Support
Support can take place on the [forum page](https://wordpress.org/support/plugin/ingenico-server-for-woocommerce), where we will try to respond as soon as possible.

#### Contributing
If you want to add code to the source code, report an issue or request an enhancement, feel free to use [GitHub](https://github.com/bigdotsoftware/ingenico-server-for-woocommerce).

== Installation ==

#### Automatic installation
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "Ingenico Server Integration Plugin" and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

#### Manual installation
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==
 
= How to add FAQ question =
* just add your FAQ questions here
 
== Screenshots ==
1. This is a text label for your first screenshot
2. Add more screenshot labels as new line
 
== Changelog ==

= 1.0.0 =
* Initial release