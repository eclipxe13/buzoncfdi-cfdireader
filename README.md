# buzoncfdi-cfdireader

Library to read and validate a Mexican CFDI (Comprobantre Fiscal por Internet)

This library open a CFDI and read it as a SimpleXML (without namespaces) for easy access.

It also validates the CFDI agains it's XSD files keeping a cache of the downloaded XSD,
also contains a local copy of TimbreFiscalDigital.xsd and cfdv32.xsd for validations without download.

This library is part of buzoncfdi project, be aware that this could change since the hole project is on development.

## Install

Install using composer

```
composer require "eclipxe/buzoncfdi-cfdireader:*"
```

## Create a reader

The `CFDIReader` class is immutable, it only perform the following checks:

* The content must be a valid XML
* The root element must be Comprobante
* The version attibute must be 3.2
* The namespaces must include http://www.sat.gob.mx/cfd/3 and http://www.sat.gob.mx/TimbreFiscalDigital
* The element Comprobante/Complemento/TimbreFiscalDigital must exists

```php
<?php
// get the contents from a file or whatever your source is
$xml = file_get_contents('some cfdi example.xml');

// create the reader
$reader = new \CFDIReader\CFDIReader($xml);

// The root element is retrieved by comprobante function, it returns always a new instance (cloned) of the root element
$cfdi = $reader->comprobante();

// all the nodes and attributes first letter is in lower case except if the name is all upper case
echo $cfdi->complemento->timbreFiscalDigital["UUID"]

## TODO

A lot of work, this is a open source project that try to offer a common and framework independent way to work with CFDI.

