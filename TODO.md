# eclipxe/buzoncfdi-cfdireader To Do

All your help is very appreciated, please contribute with testing, ideas, code, documentation, coffee, etc.

- [ ] Validator for metodoPago
- [ ] Check CFDI signature againts certificate
- [X] Integrate with Scrutinizer
- [ ] Integrate with Scrutinizer build matrix

Looks like it is not possible or easy to make scrutinizer run all the project using several versions of PHP.
This is why I'm considering return to Travis CI for continious integration and let scrutinizer only as
code review and coverage views. Feedback is welcome.
20170205: Looks like now its easy to make this on scrutinizer, lets test it later.
