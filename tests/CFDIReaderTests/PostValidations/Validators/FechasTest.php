<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\Fechas;

class FechasTest extends ValidatorsTestCase
{
    public function testValidateValid()
    {
        $this->setupWithFile('v32/valid.xml');

        $validator = new Fechas();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testInvalidFechaIsInTheFuture()
    {
        $this->setupWithFile('v32/fechas-fechafutura.xml');

        $validator = new Fechas();
        $validator->validate($this->cfdi, $this->issues);

        $expectedError = 'La fecha del documento es mayor a la fecha actual';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::ERROR)->all());
    }

    public function testInvalidFechaTimbradoIsGreaterThanFecha()
    {
        $this->setupWithFile('v32/fechas-fechatimbrado.xml');

        $validator = new Fechas();
        $validator->validate($this->cfdi, $this->issues);

        $expectedError = 'La fecha del documento es mayor a la fecha del timbrado';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::ERROR)->all());
    }

    public function testInvalidFechaTimbradoIs72HoursLate()
    {
        $this->setupWithFile('v32/fechas-timbrado72hrs.xml');

        $validator = new Fechas();
        $validator->validate($this->cfdi, $this->issues);
        $this->assertEquals([], $this->issues->messages(IssuesTypes::ERROR)->all());

        // the sample has the timbrado date is exactly 72 hours + 1 second higher than the document date
        $validator->setDelta(0);
        $validator->validate($this->cfdi, $this->issues);
        $expectedError = 'La fecha fecha del timbrado excedió las 72 horas de la fecha del documento';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::ERROR)->all());
    }

    public function testDeltaProperty()
    {
        $validator = new Fechas();
        $this->assertEquals(60, $validator->getDelta());
        // change value
        $validator->setDelta(10);
        $this->assertEquals(10, $validator->getDelta());
        // disable delta
        $validator->setDelta(0);
        $this->assertEquals(0, $validator->getDelta());
    }
}
