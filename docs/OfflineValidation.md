# Validación fuera de línea

Los archivos XSD de CFDI 3.2 y el TFD 1.0 no tenían dependencias externas.
 Sin embargo las versiones CFDI 3.3 y el TFD 1.1 sí las tienen.
 En términos técnicos el XSD está haciendo un import de otro XSD que está en la red.
 Por lo tanto, no es posible hacer la validación offline de un documento sin modificar los XSD originales.

Hay que tener en cuenta que el CFDI 3.3 depende del
 namespace `http://www.sat.gob.mx/sitio_internet/cfd/catalogos`
 que apunta al archivo `http://www.sat.gob.mx/sitio_internet/cfd/catalogos/catCFDI.xsd`,
 su descarga es de **6.4 MB**. Es ridículo un XSD de este tamaño.

Por esta razón se ha establecido una versión modificada del archivo
 `http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd` que modifica los import para que tenga copias locales
 y entonces no tenga que acudir a la descarga de los XSD para hacer las validaciones.
 La creación de esta versión modificada no está automatizada (2017-08-09) 

De esta forma, con las versiones de 2017-08-09 que contiene el proyecto, ya no es necesario descargar desde la red
 el XSD del CFD 3.3 ni de los imports ni de el TFD 1.1.
