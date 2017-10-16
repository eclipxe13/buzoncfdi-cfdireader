This project is using Semantic Versioning, meaning that a version is: MAJOR.MINOR.PATCH
  - MAJOR version when you make incompatible API changes,
  - MINOR version when you add functionality in a backwards-compatible manner, and
  - PATCH version when you make backwards-compatible bug fixes.

# Version 2.5.2
- Fix: Fechas validator take a tolerance of 60 seconds to avoid time sync errors.
  The property can be adjusted.

# Version 2.5.1
- Fix: `PostValidator` was using local issues property instead of variable.
- Fix: Probable bug when comparing null to string (phpstan complains).
- Typos and minor changes

# Version 2.5.0 - CFDI and TFD version correlation
- Add `TFDVersions` to validate correlation between CFDI and TFD versions
    - CFDI version 3.3 uses TFD version 1.1
    - CFDI version 3.2 uses TFD version 1.0
  This was set according to http://www.sat.gob.mx/informacion_fiscal/factura_electronica/Paginas/timbre_fiscal.aspx and a chat session with authorities (SAT).
- Add `TFDVersions` to `CFDIFactory::newPostValidator`


# Version 2.4.0
- Fix bug on initialize `CFDIReader\Scripts\Validate` local path
- Deprecate protected property $comprobante inside `\CFDIReader\PostValidations\Validators\AbstractValidator`
- Stop using previously deprecated property in internal validators
- Docblock `\CFDIReader\PostValidations\Validators\AbstractValidator` as access private
- Fix project description


# Version 2.3.0 - node and attribute
- Add `node` and `attribute` methods to `CFDIReader`.
  This action simplify the information extraction from the `Comprobante`node.
  - `nodes` returns `NULL` or a cloned `\SimpleXmlElement`
  - `attribute` returns a string
- Refactor validators and `CFDIReader` to use this helpers, now is more readable and simple
- Fix possible bug when `CFDIFactory::newXsltRetriever()` returns `NULL`
- Fix possible bug inside `CFDICleaner::xpathQuery()` when `\DOMXPath::query` returns `FALSE`
- Fix docblocks of object type properties that allows nulls
- Special thanks to `phpstan/phpstan` to help me catch possible bugs

# Version 2.2.0 - Validate certificate
- Create a new post validator `\CFDIReader\PostValidations\Validators\Certificado` that checks:
    - certificate number match (error)
    - emisor rfc match  (error)
    - emisor nombre match  (warning)
    - date between certificate dates (error)
    - if contains a CadenaOrigen object, verify that the sello match with the "cadena de origen" using
      the public key certificate (error). If this tails then the CFDI was modified.
- Add dependency on `eclipxe/CfdiUtils` to be able to create the "Cadena Origen" and recover
  a certificate from a cfdi.
- Add new methods on `CFDIReader\CFDIFactory` to create helper objects:
    - `newXsltRetriever(DownloaderInterface $downloader = null): ?XsltRetriever`
      Create a new instance of `XmlResourceRetriever\XsltRetriever` based on local resources path.
      This allow to create a local repository of xslt files.
      This is very similar to `newRetriever(DownloaderInterface $downloader = null): ?XsdRetriever`
      but for XSLT resources instead of XSD.
    - `newCadenaOrigen(): CadenaOrigen`
      Create a new instance of `CfdiUtils\CadenaOrigen`, using `newXsltRetriever`
    - `newCertificadoValidator(): CertificadoValidator`
      Create a new instance of `CFDIReader\PostValidations\Validators\Certificado` setting the
      `CadenaOrigen` with the returned instance of `newCadenaOrigen`

# Version 2.1.0 
- Add `source(): string` and `document(): \DOMDocument` methods to `CFDIReader\CFDIReader`
- Add docblocks to `CFDIReader\PostValidations\IssuesTypes` constants
- Add docblocks to `CFDIReader\PostValidations\ValidatorInterface` methods
- Improve test files:
    - Remove unmeaning asserts
    - By default setup the test does not require the cfdi with timbre
    - Add asset/v32/real.xml with a real cfdi
    - Use correct Emisor/nombre (from certificate)
    - Change v33/valid.xml to include: Emisor, Receptor, Fecha, Sello, Certificado, NoCertificado, FechaTimbrado
    - Update v33/valid-without-timbre.xml according to valid
- Update TODO.md

# Version 2.0.0
- Require PHP 7.0
- Add scalar type declarations and docblocks @return void
- Update dependence XmlSchemaValidator.
    - That class no longer retrieve and store a local copy of resources,
      implement `eclipxe/xmlresourceretriever` library
- Remove SchemaValidator class if favor of `CFDIReader\SchemasValidator\SchemasValidator`
- Add `CFDIReader::getVersion()`
- Remove PHP 5.6 from build matrix

# Version 1.2.0
- Make optional to require that the Comprobante contains the TimbreFiscalDigital.
  This is useful to validate before obtaining the TimbreFiscalDigital from an
  authorized third party.

# Version 1.1.1
- Improved code coverage, thanks to pull request #5 by @driftking301
- Sort assets by cfdi version
- Won't publish on packagist, just update master branch

# Version 1.1.0
- Fix `CFDIFactory::newLocator` method since it was not registering in the correct way the commonxsd files
- Add **basic support for CFDI 3.3**
  There are several thinks that can be made since version 3.3
  is not only about XML/XSD validations but also "logic" validations.
- Add `CFDIReader::allowedVersions` method that return an array with 3.2 and 3.3
- Add support for CFDI 3.3 in `CFDICleaner`
- Fix isolation to use libxml internal errors in `CFDICleaner::loadContent`
- Include local versions for `cfdv33.xsd` and `TimbreFiscalDigitalv11.xsd` modified to point to local files
  of `catCFDI.xsd` and `tdCFDI.xsd`  
- Fix documentation, include gitter chat room in README
- Fix composer.json required versions (use ^X.Y instead of @stable)
- Add doc/ files (in spanish) about the project
- Add build on scrutinizer over PHP versions 5.6, 7.0 and 7.1

# Version 1.0.11
- Add docblocks to CFDIReader\Scripts\Validate class
- Improve code coverage for CFDIReader\Scripts\Validate
- Reduce complexity of CFDIReader\Scripts\Validate::run
- Add more tests to CFDIReader\Scripts\Validate constructor
- Add phpdox.xml.dist to create documentation (testing)

# Version 1.0.10
- Move script logic to a class to be tested
- Follow insight sensiolabs recommendations
- Add sensiolabs badge

# Version 1.0.9
- Add .php_cs.dist, rename phpcs.xml to phpcs.xml.dist
- Add php-cs-fixer to composer.json
- Apply php-cs-fixer fixes
- Improve documentation, add TODO.md and CHANGELOG.md

# Version 1.0.8
- Hello scrutinizer
- Improve code quality

# Version 1.0.6
- On CFDICleaner, remove non SAT namespace declarations

# Version 1.0.5
- Include CFDICleaner, an utility to avoid must common issues on CFDI

# Version 1.0.4
- Fix mime list on default factory

# Version 1.0.3
- Better SchemaValidator

# Version 1.0.2
- Minor improvements, more docblocks

# Version 1.0.1
- SchemaValidation corrected and local taxes added to postvalidations

# Version 1.0.0
- Public initial release
