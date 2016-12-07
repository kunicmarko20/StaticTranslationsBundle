Symfony Static Translations Bundle
============
This bundle helps you generate translation for static strings in symfony application.

This bundle uses [PHPOffice/PHPExcel](https://github.com/PHPOffice/PHPExcel) bundle and

## PHPExcel bundle Requirements
 * PHP version 5.2.0 or higher
 * PHP extension php_zip enabled (required if you need PHPExcel to handle .xlsx .ods or .gnumeric files)
 * PHP extension php_xml enabled
 * PHP extension php_gd2 enabled (optional, but required for exact column width autocalculation)


## Installation

**1.**  Add to composer.json to the `require` key

```
composer require kunicmarko/static-translations
```

**2.** Register the bundle in ``app/AppKernel.php``

```
$bundles = array(
    // ...
    new KunicMarko\StaticTranslationsBundle\StaticTranslationsBundle(),
);
```

## Command

``php app/console generate:static:translation /path/to/excel/file languages``

Command accepts 2 parameters, you can run it without parameters and then you get explanation what you should do

## Parameters
**1.**  Excel file

Provide path to your Excel file that has to end with ``.xlsx``

Formating of Excel file ( can also be found [here](https://docs.google.com/spreadsheets/d/1-eIna3LE16ViSWIp91YMheAZ3nXVN1hnGsYkR_dLxjY)  ):

|   |A            | B              |                                       |
|---|-------------|----------------| --------------------------------------|
| 1 |English      |German          |                                       |
| 2 |             |                |                                       |
| 3 | About Us    | Über uns       | label.about                           |
| 4 | Contact     | Kontakt        |                                       |
| 5 | Imprint     | Impressum      | form.imprint, default.language.source |

We expect words for translation to start from line 3

You can add more languages, we only expect labels to be at last position

Labels are `optional`, there can be more than one label for same word, they just have to be divided by comma (,)

If you add labels, label names will be used for `source` tags in xml

If you want to use default language word for source and use labels for same word, you can use reserved word `default.language.source` and add it in labels part


**2.** Languages

 We expect array of language codes, divided by space e.g.  `en de fr`, use same order as in your excel file.

 `First language in array is source language and will be used for all source tags.`
