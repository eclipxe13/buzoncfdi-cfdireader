# eclipxe/buzoncfdi-cfdireader To Do

All your help is very appreciated, please contribute with testing, ideas, code, documentation, coffee, etc.

- [ ] Validator for metodoPago
- [X] Check CFDI signature againts certificate
- [ ] Move build process to travis-ci

## Validate script

- [ ] include it as a bin in composer, maybe change the name to cfdi-validate
- [ ] Allow to command line arguments like:
    - local resource path
    - require timbre fiscal digital

## CFDI 3.3

- [ ] Validate logic rules that are not validated against XSD (as in version 3.2)
- [X] Automate the creation of the modified cfdv33.xml, TimbreFiscalDigitalv11.xsd and import downloads

## Deprecate PHP 5.6 in favor of 7.0

- [X] Check known projects that are using this library for compatibility
- [ ] Add types to functions
    - [X] CFDIReader\CFDIReader
    - [X] CFDIReader\CFDICleanerException
    - [X] CFDIReader\CFDIFactory
    - [X] CFDIReader\CFDICleaner
    - [X] CFDIReader\Scripts\Validate
    - [ ] CFDIReader\PostValidations\IssuesTypes
    - [ ] CFDIReader\PostValidations\Validators
    - [ ] CFDIReader\PostValidations\ValidatorInterface
    - [ ] CFDIReader\PostValidations\Messages
    - [ ] CFDIReader\PostValidations\Issues
    - [ ] CFDIReader\PostValidations\PostValidator
    - [ ] CFDIReader\PostValidations\Validators\AbstractValidator
    - [ ] CFDIReader\PostValidations\Validators\Conceptos
    - [ ] CFDIReader\PostValidations\Validators\Fechas
    - [ ] CFDIReader\PostValidations\Validators\Totales
    - [ ] CFDIReader\PostValidations\Validators\Impuestos
    
