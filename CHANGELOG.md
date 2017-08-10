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
