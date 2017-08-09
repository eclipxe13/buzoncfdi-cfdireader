# eclipxe/buzoncfdi-cfdireader

**Library to read and validate a Mexican CFDI 3.2 and 3.3 (Comprobantre Fiscal por Internet)**

[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]
[![SensioLabsInsight][badge-sensiolabs]][sensiolabs]

This library open an Xml CFDI and read it as a SimpleXML (without namespaces) for easy access.

It also validates the CFDI agains it's XSD files
(using [Xml Schema Validator](https://github.com/eclipxe13/XmlSchemaValidator) library).

This library is part of buzoncfdi project, be aware that this could change since the hole project is on development.

## Install

Install using composer like `composer require eclipxe/buzoncfdi-cfdireader`

## Basic usage

```php
<?php
// get the contents from a file or whatever your source is
$xml = file_get_contents('some-cfdi-example.xml');

// create the reader
$reader = new \CFDIReader\CFDIReader($xml);

// The root element is retrieved by comprobante function, it returns always a new instance (cloned) of the root element
$cfdi = $reader->comprobante();

// all the nodes and attributes first letter is in lower case except if the attribute is all upper case
echo $cfdi->complemento->timbreFiscalDigital["UUID"];
```

### scripts/validate.php

Use `php scripts/validate.php [file1.xml] [file2.xml]` to test CFDIs and see the results.

## Create a reader

The `CFDIReader` class is immutable, it only perform the following checks:

* The content must be a valid XML
* The root element must be Comprobante
* The version attribute must be 3.2 or 3.3
* The namespaces must include http://www.sat.gob.mx/cfd/3 and http://www.sat.gob.mx/TimbreFiscalDigital
* The element Comprobante/Complemento/TimbreFiscalDigital must exists
* Includes a class to clean external XSD and Addendas

## Using the factory

The `CFDIFactory` allow a common way to create `CFDIReaders` using `SchemaValidator` and `PostValidator`.

The SchemaValidator is a tool that validates a XML against its multiple XSD files creating a root schema and importing
all the schemas listed in the XML by schemaLocation nodes.

The PostValidator do some specific checks about the CFDI, this includes `Conceptos`, `Fechas`, `Impuestos` and `Totales`.

## About Addendas and XML Validation

> _why don't you create valid XML files!?_

An XML file has a strict specification, if it includes XML Schemas then the specification must be followed.

The CFDI spec say that it is valid to include additional nodes inside the Addenda but it must follow the
XML specification (including namespaces and schemas).
The problem is that -since the addenda is not part of the source string- the emmiters can include additional nodes
inside the Addenda after it was signed without breaking the CFDI but breaking the XML validation.

So, can I edit a CFDI? Yes. as long you dont change any content of the source string (cadena de origen)

I has created an utility named `CFDICleaner` that removes Addendas and unused namespaces declarations.
You can use this tool to validate the document without this garbage.

```php
<?php
// $content as a clean version of the
$content = \CFDIReader\CFDICleaner::staticClean(file_get_contents('cfdi-dirty.xml'));
```

## Contributing

There is a lot of work, this is an open source project that try to offer a framework agnostic way to deal with
Mexican CFDI version 3.2 and 3.3.

Contributions are welcome! Please read [CONTRIBUTING][] for details
and don't forget to take a look in the [TODO][] and [CHANGELOG][] files.

## License

The eclipxe/buzoncfdi-cfdireader library is copyright © [Carlos C Soto](https://eclipxe.com.mx/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/eclipxe13/buzoncfdi-cfdireader/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/eclipxe13/buzoncfdi-cfdireader/blob/master/CHANGELOG.md
[todo]: https://github.com/eclipxe13/buzoncfdi-cfdireader/blob/master/TODO.md

[release]: https://github.com/eclipxe13/buzoncfdi-cfdireader/releases
[license]: https://github.com/eclipxe13/buzoncfdi-cfdireader/blob/master/LICENSE
[build]: https://scrutinizer-ci.com/g/eclipxe13/buzoncfdi-cfdireader/build-status/master
[quality]: https://scrutinizer-ci.com/g/eclipxe13/buzoncfdi-cfdireader/
[coverage]: https://scrutinizer-ci.com/g/eclipxe13/buzoncfdi-cfdireader/code-structure/master
[downloads]: https://packagist.org/packages/eclipxe/buzoncfdi-cfdireader
[sensiolabs]: https://insight.sensiolabs.com/projects/ffa9eb49-58e3-4532-acdd-f8089d46ad73

[badge-release]: https://img.shields.io/github/tag/eclipxe13/buzoncfdi-cfdireader.svg?label=version&style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/scrutinizer/build/g/eclipxe13/buzoncfdi-cfdireader/master.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/eclipxe13/buzoncfdi-cfdireader/master.svg?style=flat-square
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/eclipxe13/buzoncfdi-cfdireader/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/buzoncfdi-cfdireader.svg?style=flat-square
[badge-sensiolabs]: https://insight.sensiolabs.com/projects/ffa9eb49-58e3-4532-acdd-f8089d46ad73/mini.png
