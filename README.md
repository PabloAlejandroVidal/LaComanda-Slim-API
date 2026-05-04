# La Comanda - Slim API

API REST desarrollada en PHP y Slim Framework para la gestión de un restaurante.

El sistema modela el flujo completo de una comanda, contemplando distintos roles de empleados, sectores de preparación y estados del pedido, aplicando reglas de negocio consistentes.

---

## 🎯 Enfoque de diseño

El proyecto fue diseñado priorizando **claridad, mantenibilidad y separación de responsabilidades**, aplicando una arquitectura en capas.

Principios clave:

- centralización de la lógica de negocio en services
- controllers como capa HTTP sin lógica de negocio
- desacople del acceso a datos mediante repositories
- contratos claros mediante DTOs

Además, se tomaron decisiones de modelado para evitar inconsistencias:

- algunos estados no se almacenan explícitamente, sino que se **derivan a partir de timestamps**
- uso de enums para representar roles y estados del dominio, evitando valores mágicos

---

## 🧱 Arquitectura

La aplicación sigue una estructura por capas:

    Controller → Service → Repository → Database
                     ↓
                    DTO

- **Controllers**: reciben la request HTTP y delegan en los services  
- **Services**: contienen la lógica de negocio y reglas del sistema  
- **Repositories**: encapsulan el acceso a base de datos  
- **DTOs**: definen contratos de entrada y salida  
- **Domain**: modela estados y conceptos del negocio  

Esta organización permite mantener el código desacoplado, consistente y fácil de evolucionar.

Para una descripción completa de la arquitectura y reglas del sistema, ver:  
[Documento de arquitectura](./docs/architecture.md)

---

## 🧠 Descripción del sistema

La aplicación modela el circuito operativo de un restaurante con distintos perfiles de empleados:

- socios
- mozos
- cocineros
- bartenders
- cerveceros

Permite gestionar:

- empleados y roles
- productos organizados por sector
- mesas y estados de atención
- pedidos con múltiples detalles por sector
- flujo completo de preparación y entrega
- encuestas de satisfacción
- estadísticas operativas

---

## 🔄 Flujo de ejemplo

1. El mozo crea un pedido con productos de distintos sectores  
2. Cada sector visualiza sus pendientes  
3. Los empleados toman y preparan los productos  
4. Una vez listos todos los detalles, el pedido puede ser entregado  
5. Luego se realiza el cobro y cierre de la mesa  

---

## ⚙️ Funcionalidades principales

### Empleados
- alta y listado de empleados  
- control de acceso por rol  
- registro de ingresos al sistema  
- estadísticas de operaciones  

### Productos
- alta y listado  
- asociación a sector  

### Mesas
- alta y gestión de estados  
- estadísticas de uso y facturación  

### Pedidos
- creación con código identificador  
- carga de detalles por producto  
- preparación por sector  
- control de estados (preparación, entrega, cierre)  
- cancelación con motivo  
- seguimiento del pedido  

### Encuestas e informes
- registro de encuestas  
- estadísticas de pedidos, mesas y empleados  
- reportes de rendimiento y demoras  

---

## 🧪 Testing

El proyecto incluye pruebas mediante:

- archivos `.http` (VS Code REST Client)  
- colección de Postman para pruebas manuales  

Se recomienda configurar variables de entorno (tokens y base URL) antes de ejecutar las requests.

---

## 🛠️ Tecnologías utilizadas

- PHP 8  
- Slim Framework  
- MySQL  
- PDO  
- PHP-DI  
- JWT (autenticación)  
- Respect/Validation (validación)  
- dotenv  

---

## 📦 Estado del proyecto

La aplicación se encuentra funcional y cubre el flujo completo requerido.

El código fue refactorizado progresivamente para lograr:

- mayor consistencia entre capas  
- mejor organización  
- contratos claros  
- base mantenible  

---

## 👤 Autor

Pablo Alejandro Vidal