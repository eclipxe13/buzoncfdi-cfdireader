# Namespaces obligatorios

Al parecer y basado en la información que pude recopilar de los archivos XSD y de el anexo 20 para la versión
3.2 y 3.3 de CFDI es obligatorio que se ocupen los siguientes namespaces sin importar las diferentes versiones:

- `http://www.sat.gob.mx/cfd/3` Para CFDI 3.2 y 3.3
- `http://www.sat.gob.mx/TimbreFiscalDigital` Para TFD 1.0 y 1.1

El Anexo 20 solo refleja como mandatorio el prefijo `cfdi` para el namespace del CFDI 3.3

La versión 3.3 del CFDI debe coincidor con el TFD 1.1.
La versión 3.2 del CFDI debe coincidor con el TFD 1.0.

## CFDI 3.2 (Válido hasta 2017-11-30)

Acorde al Anexo 20 del SAT es obligatorio vincular el archivo XSD
 `http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd` al namespace
 `http://www.sat.gob.mx/cfd/3`.

El propio archivo `cfdv32.xsd` contiene el atributo `targetNamespace="http://www.sat.gob.mx/cfd/3"`

## CFDI 3.3 (Válido desde 2017-07-01)

Acorde al Anexo 20 del SAT es obligatorio vincular el archivo XSD
 `http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd` al namespace 
 `http://www.sat.gob.mx/cfd/3`. Que a pesar de los cambios es el mismo que el CFDI 3.2.
 
El propio archivo `cfdv33.xsd` contiene el atributo `targetNamespace="http://www.sat.gob.mx/cfd/3"`
 Que es igual al namespace de la versión 3.2.

> Es obligatorio el uso de la declaración: `xmlns:cfdi="http://www.sat.gob.mx/cfd/3"`

Adicionalmente menciona la forma obligatoria de la declaración, por lo que eso genera que el
prefijo del namespace sea `cfdi` 

## Timbre Fiscal Digital 1.0 (CFDI 3.2)

Acorde al Anexo 20 del SAT es obligatorio vincular el archivo XSD
 `http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigital.xsd` al namespace 
 `http://www.sat.gob.mx/TimbreFiscalDigital`.
 
El propio archivo `TimbreFiscalDigital.xsd` contiene el atributo
`targetNamespace="http://www.sat.gob.mx/TimbreFiscalDigital"`

## Timbre Fiscal Digital 1.1 (CFDI 3.3)

Acorde al Anexo 20 del SAT es obligatorio vincular el archivo XSD
 `http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd` al namespace 
 `http://www.sat.gob.mx/TimbreFiscalDigital`.
 
El propio archivo `TimbreFiscalDigitalv11.xsd` contiene el atributo
`targetNamespace="http://www.sat.gob.mx/TimbreFiscalDigital"`.
 Que a pesar de los cambios es el mismo que el Timbre Fiscal Digital 1.0.
