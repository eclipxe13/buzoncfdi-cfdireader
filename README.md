# buzoncfdi-cfdireader

Library to read and validate a Mexican CFDI 3.2 (Comprobantre Fiscal por Internet)

This library open an Xml CFDI and read it as a SimpleXML (without namespaces) for easy access.

It also validates the CFDI agains it's XSD files
(using [Xml Schema Validator](https://github.com/eclipxe13/XmlSchemaValidator) library).

This library is part of buzoncfdi project, be aware that this could change since the hole project is on development.

## Install

Install using composer

```
composer require "eclipxe/buzoncfdi-cfdireader"
```

## Create a reader

The `CFDIReader` class is immutable, it only perform the following checks:

* The content must be a valid XML
* The root element must be Comprobante
* The version attribute must be 3.2
* The namespaces must include http://www.sat.gob.mx/cfd/3 and http://www.sat.gob.mx/TimbreFiscalDigital
* The element Comprobante/Complemento/TimbreFiscalDigital must exists
* Includes a class to clean external XSD and Addendas

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

## scripts/validate.php

Use `php scripts/validate.php [file1.xml] [file2.xml]` to test CFDIs and see the results.

## Using the CFDIFactory

The CFDIFactory allow a common way to create CFDIReaders using SchemaValidator and PostValidator.

The SchemaValidator is an tool that validates a XML against its multiple XSD files creating a root schema and importing
all the schemas listed in the XML by schemaLocation nodes.

The PostValidator do some specific checks about the CFDI, this includes `Conceptos`, `Fechas`, `Impuestos` and `Totales`

## TODO

- [ ] Validator for metodoPago
- [ ] Check CFDI signature againts certificate
- [ ] Integrate with Travis CI
- [ ] Integrate with Scrutinizer
- [ ] Integrate with Insight SensioLabs
- [ ] Integrate with Coveralls

A lot of work, this is a open source project that try to offer a common and framework independent way to deal with CFDI.

