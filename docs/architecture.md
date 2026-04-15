La Comanda — Programación 3
Trabajo Práctico Final
Arquitectura de la API (PHP + Slim)
1. Objetivo

Este documento define las reglas estructurales de la API para garantizar:

consistencia

separación de responsabilidades

claridad en el flujo de la aplicación

facilidad de mantenimiento

estabilidad del contrato HTTP de la API

Ante cualquier duda de implementación, este documento es la referencia principal.

2. Arquitectura general

La aplicación sigue una arquitectura por capas:

Controller → Service → Repository → Database
                 ↓
                DTO

Cada capa tiene responsabilidades estrictas y contratos definidos.

La regla principal del sistema es:

Controller expone HTTP

Service implementa comportamiento de negocio

Repository accede a persistencia

DTO define contratos de entrada y salida

Domain modela estados y conceptos del negocio

3. Controllers

Los controllers representan la capa HTTP de la aplicación.

Responsabilidades

recibir la request HTTP

obtener datos del request

construir Request DTOs

obtener contexto de autenticación desde middleware

invocar services

devolver respuestas HTTP mediante el helper de respuestas definido por la aplicación

Los controllers no deben

contener lógica de negocio

acceder directamente a repositories

ejecutar SQL

validar reglas de negocio complejas

transformar datos de negocio

construir manualmente DTOs de salida

decidir estados del dominio

Los controllers actúan únicamente como coordinadores entre HTTP y el dominio de la aplicación.

4. Services

Los services contienen la lógica de negocio del sistema.

Existen dos tipos:

Command Services

Query Services

4.1 Command Services

Responsables de operaciones que modifican el estado del sistema.

Ejemplos:

PedidoService

MesaService

EmpleadoService

Responsabilidades

implementar reglas de negocio

validar condiciones funcionales

coordinar repositories

manejar estados del dominio

garantizar consistencia entre múltiples cambios

ejecutar transacciones cuando corresponde

devolver un resultado consistente para la capa HTTP

Toda mutación de datos debe pasar por un Command Service.

Los Command Services no deben

acceder directamente a HTTP

devolver arrays crudos provenientes del repository

delegar reglas funcionales al controller

contener detalles de infraestructura ajenos al caso de uso

4.2 Query Services

Responsables exclusivamente de consultas.

Responsabilidades

realizar consultas

agrupar datos

aplicar filtros

construir proyecciones de lectura

devolver resultados listos para exponer por la API

Los Query Services no deben

modificar datos

cambiar estados del dominio

ejecutar transacciones de escritura

depender de request o response HTTP

5. Repositories

Los repositories encapsulan el acceso a datos.

Responsabilidades

ejecutar consultas SQL

realizar joins

persistir y recuperar información

mapear resultados de base de datos a estructuras internas consistentes

devolver datos normalizados para la capa service

Los repositories pueden contener consultas complejas u optimizadas.

Los repositories no deben

contener lógica de negocio

aplicar reglas funcionales

decidir transiciones de estado

depender de HTTP

construir respuestas de API

6. Contratos entre capas

Para garantizar consistencia, cada capa debe respetar contratos explícitos de entrada y salida.

6.1 Controller → Service

Los controllers pueden pasar al service:

Request DTOs

objetos de dominio ya construidos

tipos primitivos simples cuando el caso lo justifique

Los controllers no deben pasar:

arrays HTTP sin tipar

estructuras ambiguas

datos de infraestructura sin adaptar

6.2 Service → Controller

Los services deben devolver uno de estos resultados:

Response DTOs, cuando el caso de uso expone datos por API

tipos simples, solo en casos triviales (bool, int, string, void)

colecciones tipadas y consistentes, únicamente si representan un contrato estable ya definido

Los services no deben devolver:

arrays crudos del repository

filas de base de datos sin normalizar

estructuras distintas para el mismo caso de uso según contexto

Regla

Si un endpoint expone datos estructurados, el resultado del service debe estar definido por un contrato explícito y estable.

6.3 Service → Repository

Los services delegan persistencia en repositories.

Los services pueden pedir al repository:

búsquedas por identificador

consultas filtradas

altas, bajas y modificaciones

proyecciones de lectura específicas

Los services no deben:

construir SQL

depender del esquema físico de tablas más de lo necesario

distribuir reglas de negocio entre múltiples repositories sin coordinación

6.4 Repository → Service

Los repositories deben devolver:

estructuras de datos consistentes y normalizadas

entidades internas cuando aplique

tipos de dominio cuando corresponda, por ejemplo enums

Los repositories no deben devolver:

resultados heterogéneos para el mismo método

valores sin mapear cuando el dominio ya tiene una representación formal

estructuras preparadas para HTTP

Ejemplo de retorno válido de repository
[
    'id' => 'A1234',
    'estado' => EstadoMesa::CERRADA
]
7. DTOs

Los DTOs definen la estructura de entrada y salida de la aplicación.

7.1 Request DTOs

Ubicación:

App\DTO\Request
Responsabilidades

validar datos de entrada

normalizar datos

tipar el input recibido

rechazar estructuras inválidas antes de llegar al negocio

Se construyen mediante métodos estáticos.

Ejemplo conceptual:

PedidoRequest::fromArray($data)
Regla

Toda request con estructura relevante debe convertirse en un Request DTO antes de ingresar al service.

7.2 Response DTOs

Ubicación:

App\DTO\Response
Responsabilidades

definir el formato de salida del caso de uso

desacoplar la estructura interna de la base de datos

estabilizar el contrato público de la API

evitar que cambios internos impacten directamente en la respuesta HTTP

Regla general

Se deben usar Response DTOs siempre que la salida sea expuesta por la API y no sea un caso trivial.

Son obligatorios cuando

hay joins complejos

existen agregaciones

la salida no coincide directamente con la estructura de base de datos

el endpoint es público o crítico

el resultado debe mantenerse estable aunque cambie persistencia interna

8. Domain / Enums

Ubicación:

App\Domain
Responsabilidades

representar estados del negocio

centralizar valores del dominio

eliminar strings o números mágicos

expresar reglas conceptuales del sistema

Los objetos de dominio no deben depender de:

HTTP

base de datos

infraestructura

formato de respuesta

9. Estados del sistema
9.1 Estado del detalle de pedido

El estado del detalle se deriva a partir de timestamps.

Estado	Condición
pendiente	hora_asigno es NULL
en_preparacion	hora_asigno no es NULL y hora_preparo es NULL
listo	hora_preparo no es NULL y hora_entrego es NULL
entregado	hora_entrego no es NULL

El estado no se guarda como campo persistente si ya puede derivarse de forma confiable.

9.2 Estado de mesa

Los estados de mesa son:

CERRADA

ESPERANDO_PEDIDO

COMIENDO

PAGANDO

El control de estos estados se implementa en los services.

La transición entre estados debe ser validada por reglas de negocio, no por el controller ni por el repository.

Regla

No deben definirse estados en enums, responses o services que no formen parte del modelo oficial del dominio, salvo que exista una decisión explícita de rediseño documentada.

10. Autenticación y Token

La autenticación se realiza mediante JWT.

El middleware de autenticación:

valida el token

valida el rol del empleado

inyecta el token decodificado en el request

Los controllers obtienen el TokenPayload mediante:

$token = $this->getTokenPayload($request); que lo genera a partir del token de

$request->getAttribute('decodedToken')

El token debe transformarse en un objeto de dominio o DTO interno antes de utilizarse.

No se debe utilizar el array decodificado directamente en lógica de negocio.

11. Manejo de errores

La aplicación utiliza un sistema centralizado de manejo de errores basado en excepciones.

Las excepciones del sistema heredan de una base común:

HttpBaseException

Cada excepción define:

código HTTP

código interno de estado

mensaje

errores adicionales opcionales

Los errores son interceptados por un middleware global de manejo de errores.

Este middleware transforma las excepciones en respuestas HTTP consistentes.

Regla

Las capas internas lanzan excepciones; la capa HTTP las traduce a respuesta.

Alcance

Una excepción puede originarse en:

DTOs

controllers

services

repositories

middleware

12. Formato de respuestas

Todas las respuestas de la API deben generarse mediante un único helper/factory de respuestas definido por la aplicación.

Ese componente debe ser la única fuente de verdad del formato HTTP.

Formato general:

Respuesta exitosa
{
  "success": true,
  "status": "SUCCESS",
  "message": "...",
  "data": {}
}
Error simple
{
  "success": false,
  "status": "ERROR_CODE",
  "message": "Descripción del error"
}
Error con detalles
{
  "success": false,
  "status": "ERROR_CODE",
  "message": "Descripción del error",
  "errors": {}
}

El campo errors solo aparece cuando existen errores adicionales.

Regla

No se deben construir respuestas JSON manualmente en controllers o services.

13. TransactionManager

La aplicación define una abstracción de manejo de transacciones.

Ubicación:

App\Contracts\TransactionManager

Implementación actual:

App\Infrastructure\PdoTransactionManager

Los services utilizan la interfaz, no la implementación concreta.

Esto desacopla la lógica de negocio del mecanismo de persistencia.

Regla obligatoria

Toda operación que involucre múltiples escrituras, o una escritura cuyo fallo parcial deje el sistema en estado inconsistente, debe ejecutarse dentro de una transacción.

Ejemplos típicos

crear pedido y sus detalles

asignar preparación y registrar responsable

cerrar pedido y actualizar estado de mesa

14. Validación

La validación se divide por nivel de responsabilidad.

Validación estructural

Corresponde a Request DTOs.

Incluye:

campos obligatorios

tipos

formato

longitudes

normalización

Validación de negocio

Corresponde a Services.

Incluye:

existencia o inexistencia requerida

permisos funcionales

transiciones válidas de estado

consistencia entre entidades

reglas temporales o de operación

Regla

Los controllers no validan lógica de negocio.

15. Reglas de diseño del proyecto
Regla 1

Los controllers coordinan HTTP.
Los services implementan la lógica.

Regla 2

Los repositories acceden a la base de datos.
Los services representan los casos de uso.

Regla 3

Los estados del dominio se derivan cuando corresponde; no se duplican sin necesidad.

Regla 4

Los Query Services nunca modifican datos.

Regla 5

Toda salida pública y no trivial debe tener un contrato estable.

Regla 6

Ninguna capa debe devolver estructuras ambiguas o inconsistentes.

Regla 7

La respuesta HTTP no define el dominio; el dominio define qué puede exponerse.

Regla 8

Ante duda entre comodidad y consistencia, se prioriza consistencia arquitectónica.

16. Flujo general de una operación
Flujo exitoso
Request HTTP
    ↓
Routing
    ↓
Middleware
    ↓
Controller
    ↓
Request DTO
    ↓
Service
    ↓
Repository
    ↓
Base de datos
    ↓
Service
    ↓
Response DTO / resultado estable
    ↓
Response Helper / Factory
    ↓
HTTP Response
Flujo con error
Cualquier capa
    ↓
throw Exception
    ↓
ErrorHandlerMiddleware
    ↓
Response Helper / Factory
    ↓
HTTP Response
17. Estado actual de la arquitectura

La implementación actual busca consolidarse sobre estas reglas:

separación clara de capas

manejo centralizado de errores

autenticación mediante middleware

DTOs para entrada

services con lógica de negocio

repositories especializados

respuestas HTTP consistentes

No obstante, al estar en proceso de refactorización, cualquier implementación existente que contradiga este documento debe considerarse deuda técnica y corregirse progresivamente.

18. Criterio de resolución de dudas

Si una implementación concreta entra en conflicto con este documento, prevalece este documento.

Si el documento no contempla un caso particular, la decisión debe respetar estas prioridades:

separación de responsabilidades

consistencia de contratos entre capas

claridad del flujo

estabilidad del contrato público de la API

facilidad de mantenimiento