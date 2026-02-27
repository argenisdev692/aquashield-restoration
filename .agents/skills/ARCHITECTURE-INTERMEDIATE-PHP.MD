# PHP 8.5 · Laravel 12 · Intermediate Enterprise Architecture 2026

# Designed for: 2 Senior Developers

# Philosophy: Hexagonal + DDD principles, without ceremony overhead.

# Same solid foundations — less boilerplate, more productivity.

#

# WHAT IS KEPT (non-negotiable):

# ✅ Hexagonal Architecture (Ports & Adapters)

# ✅ Domain Entities + Value Objects (pure, framework-agnostic)

# ✅ Repository Pattern with Ports (interfaces in Domain)

# ✅ Eloquent ↔ Domain Mappers (@internal enforced)

# ✅ CQRS (Commands/Queries — direct handlers, no formal Bus)

# ✅ DB Transactions (UnitOfWork + TransactionalHandler)

# ✅ Laravel Reverb (WebSockets / real-time)

# ✅ Queue system (Laravel Horizon + RabbitMQ adapter)

# ✅ Storage (S3 + local + SpatieMediaLibrary)

# ✅ Mail (Resend adapter + React Email templates)

# ✅ Circuit Breaker + Retry + Rate Limiter

# ✅ Auth (Sanctum + OTP + OAuth + Guards)

# ✅ PHPStan level 8 + @internal on Eloquent Models

# ✅ OpenTelemetry + Structured Logging (Monolog)

# ✅ Health checks + Prometheus metrics

# ✅ PEST tests (Unit + Integration + Feature)

# ✅ Excel (maatwebsite/excel) + PDF (laravel-dompdf) Exports

#

# WHAT IS SIMPLIFIED (reduces overhead for small teams):

# ❌ No formal CommandBus/QueryBus — handlers called directly

# ❌ No IntegrationEvents between contexts — use Laravel Events

# ❌ No ACL (Anti-Corruption Layer) between contexts — unnecessary with 2 devs

# ❌ No PHPArkitect — PHPStan level 8 is sufficient enforcement

# ❌ No separate Contracts/ Published Language per context

# ❌ No Domain Subscribers — use standard Laravel Event Listeners

#

# ──────────────────────────────────────────────────────────────────────────────

# HOW TO CREATE A NEW CRUD MODULE

# ──────────────────────────────────────────────────────────────────────────────

# 1. Copy the {YourModule}/ template at the bottom of this file

# 2. Replace {YourModule} with your module name (e.g. Products)

# 3. Replace {YourEntity} with your entity name (e.g. Product)

# 4. Replace {YourId} with your VO id name (e.g. ProductId)

# 5. Create Domain/Entities/{YourEntity}.php — pure domain logic, no Eloquent

# 6. Create Domain/ValueObjects/{YourId}.php — readonly + clone with

# 7. Create Domain/Ports/{YourEntity}Repository.php — interface only

# 8. Create Application/Commands/ handlers — write side (no Bus, inject repo directly)

# → MUST dispatch Domain Events (e.g., UserCreated) for write operations

# → MUST implement Domain Event Subscribers to manage Cache::forget invalidations

# 9. Create Application/Queries/ handlers — read side (Eloquent OK here)

# → MUST implement Cache::remember with TTL logic

# 10. Create Infrastructure/Persistence/Eloquent/Models/{YourEntity}EloquentModel.php

# → Always add @internal docblock

# → MUST use Spatie\Activitylog\Traits\LogsActivity and implement getActivitylogOptions()

# 11. Create Infrastructure/Persistence/Mappers/{YourEntity}Mapper.php

# → This is the ONLY class allowed to know both Domain Entity and EloquentModel

# 12. Create Infrastructure/Persistence/Repositories/Eloquent{YourEntity}Repository.php

# → Implements {YourEntity}Repository port. Uses Mapper internally.

# 13. Bind Port → Repository in {YourModule}ServiceProvider.php

# 14. Create Infrastructure/Http/Controllers/{YourEntity}Controller.php

# 15. Create Infrastructure/Http/Requests/ + Resources/

# 16. Register routes in Infrastructure/Routes/api.php and Infrastructure/Routes/web.php

# 17. If the module requires user_id by relationship, add it as well as the belongsTo or hasMany from User to that table

# 18. Write PEST tests in Tests/

# 19. Create Infrastructure/Persistence/Export/{YourEntity}ExcelExport.php and PdfExport.php

# 20. Implement {YourEntity}ExportController.php to handle the /export endpoint

# ──────────────────────────────────────────────────────────────────────────────

src/
│
├── Shared/ # 🔧 Cross-cutting — shared across all modules
│ │
│ ├── Domain/ # Pure shared domain (no framework dependencies)
│ │ │
│ │ ├── Exceptions/ # Base domain exceptions
│ │ │ ├── DomainException.php # Base for all domain exceptions
│ │ │ ├── EntityNotFoundException.php
│ │ │ ├── ValidationException.php
│ │ │ ├── UnauthorizedException.php
│ │ │ ├── BusinessRuleViolationException.php
│ │ │ ├── ConcurrencyException.php # Optimistic locking / race conditions
│ │ │ ├── InvariantViolationException.php # Aggregate invariant broken
│ │ │ └── IntegrationException.php # External service failure
│ │ │
│ │ ├── ValueObjects/ # Shared Value Objects (readonly + clone with PHP 8.4)
│ │ │ ├── Uuid.php # readonly — ramsey/uuid
│ │ │ ├── Email.php # readonly — validated on construction
│ │ │ ├── PhoneNumber.php # readonly
│ │ │ ├── Money.php # readonly — amount + currency
│ │ │ ├── DateRange.php # readonly — from + to with validation
│ │ │ ├── Timestamp.php # readonly — immutable datetime wrapper
│ │ │ └── Url.php # readonly — validated URL
│ │ │
│ │ ├── Entities/
│ │ │ └── AggregateRoot.php # Base aggregate — holds domain events list
│ │ │
│ │ └── Ports/ # Shared infrastructure interfaces (ports)
│ │ ├── CachePort.php
│ │ ├── QueuePort.php
│ │ ├── LoggerPort.php
│ │ ├── StoragePort.php
│ │ ├── NotificationPort.php
│ │ └── ExportPort.php # 🔥 Excel + PDF exports
│ │
│ ├── Application/ # Shared application layer
│ │ │
│ │ ├── DTOs/ # Base DTOs (spatie/laravel-data)
│ │ │ ├── BaseDTO.php # extends Data
│ │ │ ├── PaginationDTO.php # readonly — page, perPage, total
│ │ │ └── FilterDTO.php # readonly — search, dateFrom, dateTo, sortBy, sortDir
│ │ │
│ │ └── Transactions/ # DB transaction support
│ │ ├── TransactionInterface.php
│ │ └── TransactionalHandler.php # Wraps handler execution in DB::transaction()
│ │ # Usage: extend this in any Command Handler
│ │ # that needs atomic DB operations
│ │
│ └── Infrastructure/ # Shared infrastructure adapters
│ │
│ ├── Cache/
│ │ ├── CacheInterface.php
│ │ ├── RedisAdapter.php
│ │ └── InMemoryCacheAdapter.php # For tests
│ │
│ ├── Queue/ # Queue system
│ │ ├── QueueInterface.php
│ │ ├── LaravelQueueAdapter.php # Default — uses Laravel + Horizon
│ │ ├── RabbitMQAdapter.php # For heavy workloads / decoupled services
│ │ └── SqsAdapter.php # AWS SQS alternative
│ │
│ ├── Broadcasting/ # Real-time — Laravel Reverb
│ │ ├── BroadcastingInterface.php
│ │ ├── ReverbAdapter.php # laravel/reverb — primary
│ │ └── PusherAdapter.php # Fallback
│ │
│ ├── Storage/ # File storage
│ │ ├── StorageInterface.php
│ │ ├── S3StorageAdapter.php # league/flysystem-aws-s3-v3
│ │ ├── LocalStorageAdapter.php
│ │ └── SpatieMediaLibraryAdapter.php # spatie/laravel-medialibrary — advanced media
│ │
│ ├── Mail/ # Email system
│ │ ├── MailInterface.php
│ │ ├── ResendAdapter.php # resend/resend-laravel — primary
│ │ ├── MailgunAdapter.php # Fallback
│ │ └── ReactEmailTemplateRenderer.php # React Email templates (optional)
│ │
│ ├── Export/ # 🔥 Export system
│ │ ├── ExportInterface.php
│ │ ├── ExcelAdapter.php # maatwebsite/excel v3
│ │ ├── PdfAdapter.php # barryvdh/laravel-dompdf v3
│ │ └── PdfTemplateRenderer.php # Handles stylized PDF themes with logo
│ │
│ ├── Logging/ # Structured logging — Monolog as central bus
│ │ ├── ApplicationLogger.php
│ │ ├── Handlers/
│ │ │ ├── OpenTelemetryMonologHandler.php # Monolog → OTEL Collector (OTLP/gRPC)
│ │ │ └── StructuredJsonHandler.php # Structured stdout for k8s/cloud
│ │ └── Processors/
│ │ ├── TraceContextProcessor.php # Injects trace_id + span_id into every log
│ │ ├── CorrelationIdProcessor.php # Injects correlation_id from the request
│ │ └── RequestContextProcessor.php # Injects user_id, route
│ │
│ ├── Observability/ # Metrics, tracing, health
│ │ │
│ │ ├── Tracing/ # OpenTelemetry SDK 1.x (CNCF Graduated)
│ │ │ ├── OpenTelemetryAdapter.php # OTLP/gRPC → Collector (Jaeger / Zipkin)
│ │ │ ├── InstrumentationProvider.php # Auto-instruments SQL, HTTP client, Redis
│ │ │ └── SpanEnricher.php
│ │ │
│ │ ├── Metrics/
│ │ │ ├── PrometheusAdapter.php # GET /metrics → Prometheus scrape
│ │ │ └── PrometheusController.php
│ │ │
│ │ └── HealthCheck/
│ │ ├── HealthCheckController.php # GET /health → aggregated status
│ │ ├── HealthCheckAggregator.php
│ │ ├── DatabaseHealthCheck.php
│ │ ├── RedisHealthCheck.php
│ │ ├── QueueHealthCheck.php
│ │ ├── ReverbHealthCheck.php
│ │ ├── StorageHealthCheck.php # S3 / local disk
│ │ └── ExternalServiceHealthCheck.php # Resend, S3, etc.
│ │
│ ├── Resilience/ # Resilience patterns
│ │ ├── CircuitBreaker/
│ │ │ ├── CircuitBreaker.php
│ │ │ ├── CircuitBreakerInterface.php
│ │ │ ├── CircuitBreakerState.php # open / closed / half-open
│ │ │ └── CircuitBreakerMetricsExporter.php # Exports state → Prometheus
│ │ ├── RateLimiter/
│ │ │ ├── RateLimiter.php
│ │ │ └── RateLimiterInterface.php
│ │ └── Retry/
│ │ ├── RetryPolicy.php
│ │ └── ExponentialBackoff.php
│ │
│ ├── Persistence/
│ │ └── Transactions/
│ │ ├── DatabaseTransaction.php # Implements TransactionInterface
│ │ └── UnitOfWork.php # Coordinates persist + flush + rollback
│ │
│ └── Audit/ # Cross-cutting audit
│ ├── AuditInterface.php
│ ├── SpatieActivityLogAdapter.php # spatie/laravel-activitylog
│ └── AuditableInterface.php # Implement on any auditable Eloquent model
│
├── Middleware/ # Global HTTP middleware
│ ├── AuthenticationMiddleware.php
│ ├── AuthorizationMiddleware.php
│ ├── CorrelationIdMiddleware.php
│ ├── TraceContextMiddleware.php # Propagates OpenTelemetry trace_id
│ ├── RateLimitMiddleware.php
│ └── SecurityHeadersMiddleware.php
│
└── Providers/ # Core service providers
├── SharedServiceProvider.php # Binds all shared ports → adapters
├── BroadcastingServiceProvider.php # Reverb config
└── ObservabilityServiceProvider.php # OTEL + Prometheus + HealthChecks

└── Modules/ # 📦 Feature Modules
│
├── Auth/ # 🔐 MODULE: Authentication
│ │
│ ├── Providers/
│ │ └── AuthServiceProvider.php # Binds ports → repositories + registers guards
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ ├── Domain/
│ │ │ │ ├── OtpValidatorTest.php
│ │ │ │ └── PasswordHasherTest.php
│ │ │ └── Application/
│ │ │ ├── LoginHandlerTest.php
│ │ │ └── RegisterUserHandlerTest.php
│ │ ├── Integration/
│ │ │ └── EloquentUserRepositoryTest.php
│ │ └── Feature/
│ │ ├── LoginTest.php
│ │ ├── OtpFlowTest.php
│ │ ├── SetupPasswordTest.php # First-access link flow
│ │ └── OAuthTest.php
│ │
│ ├── Domain/ # ⭐ Pure domain — zero framework imports
│ │ │
│ │ ├── Entities/
│ │ │ ├── User.php # Root aggregate — holds identity + status
│ │ │ ├── Session.php
│ │ │ └── RefreshToken.php
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── UserId.php # readonly
│ │ │ ├── UserEmail.php # readonly — validated
│ │ │ ├── HashedPassword.php # readonly — never stores raw password
│ │ │ ├── Password.php # readonly — raw, for hashing only
│ │ │ ├── OtpCode.php # readonly
│ │ │ ├── SetupToken.php # readonly — first-access / admin reset token
│ │ │ ├── SessionId.php # readonly
│ │ │ └── IpAddress.php # readonly
│ │ │
│ │ ├── Enums/
│ │ │ ├── UserStatus.php # active, suspended, pending*setup, banned
│ │ │ ├── OtpStatus.php # pending, verified, expired, revoked
│ │ │ ├── SessionStatus.php # active, expired, revoked
│ │ │ └── TokenType.php # access, refresh, api, setup
│ │ │
│ │ ├── Ports/ # Repository interfaces — implemented in Infrastructure
│ │ │ ├── UserRepositoryPort.php
│ │ │ ├── OtpRepositoryPort.php
│ │ │ ├── SessionRepositoryPort.php
│ │ │ └── RefreshTokenRepositoryPort.php
│ │ │
│ │ ├── Services/ # Domain services (stateless, pure logic)
│ │ │ ├── OtpGenerator.php # spatie/laravel-one-time-passwords
│ │ │ ├── OtpValidator.php
│ │ │ ├── PasswordHasher.php
│ │ │ └── TokenGenerator.php
│ │ │
│ │ ├── Policies/ # Domain authorization rules
│ │ │ ├── CanLoginPolicy.php
│ │ │ ├── CanResetPasswordPolicy.php
│ │ │ └── MaxOtpAttemptsPolicy.php
│ │ │
│ │ ├── Events/ # Domain events (dispatched via Laravel Events)
│ │ │ ├── UserRegistered.php
│ │ │ ├── UserLoggedIn.php
│ │ │ ├── UserLoggedOut.php
│ │ │ ├── OtpGenerated.php
│ │ │ ├── OtpVerified.php
│ │ │ ├── PasswordChanged.php
│ │ │ ├── FirstTimePasswordSet.php
│ │ │ └── SessionExpired.php
│ │ │
│ │ ├── Specifications/ # Business rule specifications
│ │ │ ├── UserMustHaveUniqueEmail.php
│ │ │ └── OtpMustNotBeExpired.php
│ │ │
│ │ └── Exceptions/
│ │ ├── InvalidCredentialsException.php
│ │ ├── UserNotFoundException.php
│ │ ├── UserAlreadyExistsException.php
│ │ ├── InvalidOtpException.php
│ │ ├── ExpiredOtpException.php
│ │ ├── SetupTokenExpiredException.php
│ │ └── PasswordAlreadySetException.php
│ │
│ ├── Application/ # ⚙️ Use cases — thin orchestrators
│ │ │
│ │ ├── Commands/ # Write operations — called directly (no Bus)
│ │ │ │
│ │ │ ├── RegisterUser/
│ │ │ │ ├── RegisterUserCommand.php # DTO: name, email, password
│ │ │ │ └── RegisterUserHandler.php # Creates User entity → saves → dispatches UserRegistered
│ │ │ │
│ │ │ ├── Login/
│ │ │ │ ├── LoginCommand.php # DTO: email, password, ip
│ │ │ │ └── LoginHandler.php # Validates credentials → creates Session → returns tokens
│ │ │ │
│ │ │ ├── LoginWithOtp/
│ │ │ │ ├── LoginWithOtpCommand.php
│ │ │ │ └── LoginWithOtpHandler.php
│ │ │ │
│ │ │ ├── Logout/
│ │ │ │ ├── LogoutCommand.php
│ │ │ │ └── LogoutHandler.php
│ │ │ │
│ │ │ ├── SendOtp/
│ │ │ │ ├── SendOtpCommand.php
│ │ │ │ └── SendOtpHandler.php
│ │ │ │
│ │ │ ├── VerifyOtp/
│ │ │ │ ├── VerifyOtpCommand.php
│ │ │ │ └── VerifyOtpHandler.php
│ │ │ │
│ │ │ ├── ChangePassword/
│ │ │ │ ├── ChangePasswordCommand.php
│ │ │ │ └── ChangePasswordHandler.php # extends TransactionalHandler
│ │ │ │
│ │ │ ├── ResetPassword/
│ │ │ │ ├── RequestPasswordResetCommand.php
│ │ │ │ ├── RequestPasswordResetHandler.php
│ │ │ │ ├── ResetPasswordCommand.php
│ │ │ │ └── ResetPasswordHandler.php
│ │ │ │
│ │ │ ├── SetFirstTimePassword/ # User follows welcome link and sets password
│ │ │ │ ├── SetFirstTimePasswordCommand.php
│ │ │ │ └── SetFirstTimePasswordHandler.php
│ │ │ │
│ │ │ └── RefreshToken/
│ │ │ ├── RefreshTokenCommand.php
│ │ │ └── RefreshTokenHandler.php
│ │ │
│ │ ├── Queries/ # Read operations — Eloquent used directly here
│ │ │ ├── GetUser/
│ │ │ │ ├── GetUserQuery.php
│ │ │ │ └── GetUserHandler.php # Returns UserReadModel (not Domain Entity)
│ │ │ ├── GetActiveSessions/
│ │ │ │ ├── GetActiveSessionsQuery.php
│ │ │ │ └── GetActiveSessionsHandler.php
│ │ │ ├── VerifyToken/
│ │ │ │ ├── VerifyTokenQuery.php
│ │ │ │ └── VerifyTokenHandler.php
│ │ │ └── ReadModels/ # Read-only projections — Eloquent result shaped as DTO
│ │ │ ├── UserReadModel.php
│ │ │ └── SessionReadModel.php
│ │ │
│ │ ├── EventListeners/ # React to domain events (async, post-commit)
│ │ │ ├── SendWelcomeEmailOnRegistered.php
│ │ │ ├── SendOtpEmailOnOtpGenerated.php
│ │ │ └── InvalidateSessionOnPasswordChange.php
│ │ │
│ │ ├── Permissions/ # spatie/laravel-permission definitions
│ │ │ ├── RoleDefinitions.php # Declarative list: super_admin, admin, user
│ │ │ └── PermissionDefinitions.php # Declarative list of all permissions
│ │ │
│ │ └── Services/ # Application-level orchestration services
│ │ ├── AuthenticationService.php # Orchestrates Fortify + Sanctum
│ │ ├── PermissionService.php # Wraps spatie/laravel-permission
│ │ └── AuthorizationService.php
│ │
│ └── Infrastructure/
│ │
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Api/
│ │ │ │ ├── AuthController.php # POST /login, /logout, /refresh
│ │ │ │ ├── OtpController.php # POST /otp/send, /otp/verify
│ │ │ │ ├── PasswordController.php # POST /password/reset, /password/change
│ │ │ │ └── SetupPasswordController.php # GET+POST /auth/setup-password/{token}
│ │ │ └── Web/
│ │ │ └── LoginPageController.php # Inertia — GET /login
│ │ │
│ │ ├── Requests/
│ │ │ ├── LoginRequest.php
│ │ │ ├── RegisterRequest.php
│ │ │ ├── SendOtpRequest.php
│ │ │ ├── VerifyOtpRequest.php
│ │ │ ├── ChangePasswordRequest.php
│ │ │ ├── ResetPasswordRequest.php
│ │ │ └── SetFirstTimePasswordRequest.php
│ │ │
│ │ ├── Resources/
│ │ │ ├── UserResource.php
│ │ │ └── SessionResource.php
│ │ │
│ │ └── Middleware/
│ │ ├── OtpRateLimitMiddleware.php
│ │ └── CheckSessionValidity.php
│ │
│ ├── WebSocket/ # Real-time auth events (Reverb)
│ │ ├── Channels/
│ │ │ ├── UserChannel.php
│ │ │ └── SessionChannel.php
│ │ └── Events/
│ │ ├── UserLoggedInBroadcast.php
│ │ └── SessionExpiredBroadcast.php
│ │
│ ├── Persistence/
│ │ ├── Eloquent/
│ │ │ ├── Models/
│ │ │ │ ├── UserEloquentModel.php # @internal — only Mappers/Repositories use this
│ │ │ │ │ # HasRoles (spatie), HasApiTokens (sanctum), SoftDeletes
│ │ │ │ ├── OtpEloquentModel.php # @internal
│ │ │ │ ├── SessionEloquentModel.php # @internal
│ │ │ │ └── RefreshTokenEloquentModel.php # @internal
│ │ │ ├── Migrations/
│ │ │ │ ├── 2026_01_create_users_table.php
│ │ │ │ ├── 2026_02_create_otp_codes_table.php
│ │ │ │ ├── 2026_03_create_sessions_table.php
│ │ │ │ └── 2026_04_create_refresh_tokens_table.php
│ │ │ ├── Seeders/
│ │ │ │ ├── RolesSeeder.php # spatie/laravel-permission
│ │ │ │ ├── PermissionsSeeder.php
│ │ │ │ └── SuperAdminSeeder.php
│ │ │ └── Factories/
│ │ │ ├── UserFactory.php
│ │ │ └── OtpFactory.php
│ │ │
│ │ ├── Repositories/
│ │ │ ├── EloquentUserRepository.php # Implements UserRepositoryPort — uses UserMapper
│ │ │ ├── EloquentOtpRepository.php
│ │ │ ├── EloquentSessionRepository.php
│ │ │ └── EloquentRefreshTokenRepository.php
│ │ │
│ │ └── Mappers/ # Eloquent ↔ Domain Entity translation
│ │ ├── UserMapper.php # ONLY class that imports both User entity + UserEloquentModel
│ │ ├── OtpMapper.php
│ │ ├── SessionMapper.php
│ │ └── RefreshTokenMapper.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ ├── SendOtpEmailJob.php
│ │ ├── SendOtpSmsJob.php
│ │ ├── SendWelcomeSetupEmailJob.php # Welcome email with first-access link
│ │ ├── SendPasswordResetEmailJob.php
│ │ └── SendAdminPasswordResetEmailJob.php
│ │
│ ├── ExternalServices/
│ │ ├── OAuth/ # laravel/socialite
│ │ │ ├── OAuthProviderInterface.php
│ │ │ ├── GoogleOAuthAdapter.php
│ │ │ ├── GithubOAuthAdapter.php
│ │ │ └── MicrosoftOAuthAdapter.php
│ │ │
│ │ └── Authorization/
│ │ ├── Gates/
│ │ │ ├── ManageUsersGate.php
│ │ │ └── ViewAuditLogGate.php
│ │ └── Guards/
│ │ ├── SanctumGuard.php
│ │ ├── SessionGuard.php
│ │ └── OtpGuard.php
│ │
│ └── Routes/
│ ├── api.php
│ ├── web.php
│ └── channels.php # Reverb channel definitions
│
│
├── Users/ # 👤 MODULE: User Management (admin CRUD)
│ │
│ ├── Providers/
│ │ └── UsersServiceProvider.php # Binds UserRepositoryPort → EloquentUserRepository
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ ├── Domain/
│ │ │ │ └── UserStatusManagerTest.php
│ │ │ └── Application/
│ │ │ ├── CreateUserHandlerTest.php
│ │ │ └── SuspendUserHandlerTest.php
│ │ ├── Integration/
│ │ │ └── EloquentUserRepositoryTest.php
│ │ └── Feature/
│ │ ├── AdminCrudUserTest.php # Full CRUD as Super Admin
│ │ ├── SetFirstTimePasswordTest.php # Welcome flow → link → set password
│ │ └── AdminResetPasswordTest.php # Admin resets → email → user sets new one
│ │
│ ├── Domain/
│ │ ├── Entities/
│ │ │ ├── User.php # Root entity — status, profile, suspension logic
│ │ │ └── UserProfile.php
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── UserId.php # readonly
│ │ │ ├── FullName.php # readonly
│ │ │ ├── Avatar.php # readonly
│ │ │ └── Bio.php # readonly
│ │ │
│ │ ├── Enums/
│ │ │ └── UserStatus.php # active, suspended, pending_setup, deleted
│ │ │
│ │ ├── Ports/
│ │ │ ├── UserRepositoryPort.php
│ │ │ └── UserProfileRepositoryPort.php
│ │ │
│ │ ├── Services/
│ │ │ └── UserStatusManager.php # suspend / activate / ban business rules
│ │ │
│ │ ├── Events/
│ │ │ ├── UserCreatedByAdmin.php # → triggers WelcomeSetupEmailJob
│ │ │ ├── UserSuspended.php
│ │ │ ├── UserActivated.php
│ │ │ ├── UserDeleted.php
│ │ │ └── AvatarChanged.php
│ │ │
│ │ ├── Specifications/
│ │ │ └── UserEmailMustBeUnique.php
│ │ │
│ │ └── Exceptions/
│ │ ├── UserNotFoundException.php
│ │ ├── UserAlreadyExistsException.php
│ │ └── InvalidAvatarException.php
│ │
│ ├── Application/
│ │ ├── Commands/
│ │ │ ├── CreateUser/
│ │ │ │ ├── CreateUserCommand.php # name, email, role — no password (user sets it via link)
│ │ │ │ └── CreateUserHandler.php # Creates user status=pending_setup → UserCreatedByAdmin event
│ │ │ │
│ │ │ ├── UpdateUser/
│ │ │ │ ├── UpdateUserCommand.php # name, email, role, status
│ │ │ │ └── UpdateUserHandler.php
│ │ │ │
│ │ │ ├── SuspendUser/
│ │ │ │ ├── SuspendUserCommand.php
│ │ │ │ └── SuspendUserHandler.php
│ │ │ │
│ │ │ ├── ActivateUser/
│ │ │ │ ├── ActivateUserCommand.php
│ │ │ │ └── ActivateUserHandler.php
│ │ │ │
│ │ │ ├── DeleteUser/
│ │ │ │ ├── DeleteUserCommand.php # Soft delete
│ │ │ │ └── DeleteUserHandler.php
│ │ │ │
│ │ │ ├── AdminResetPassword/
│ │ │ │ ├── AdminResetPasswordCommand.php
│ │ │ │ └── AdminResetPasswordHandler.php # Generates SetupToken → sends email
│ │ │ │
│ │ │ └── UpdateProfile/
│ │ │ ├── UpdateProfileCommand.php
│ │ │ └── UpdateProfileHandler.php
│ │ │
│ │ ├── Queries/
│ │ │ ├── ListUsers/
│ │ │ │ ├── ListUsersQuery.php # filters: status, role, search, dateRange
│ │ │ │ └── ListUsersHandler.php # Eloquent directly — returns paginated ReadModel
│ │ │ │
│ │ │ ├── GetUser/
│ │ │ │ ├── GetUserQuery.php
│ │ │ │ └── GetUserHandler.php
│ │ │ │
│ │ │ └── ReadModels/
│ │ │ ├── UserReadModel.php
│ │ │ ├── UserListReadModel.php # Compact — for admin table
│ │ │ └── UserProfileReadModel.php
│ │ │
│ │ └── EventListeners/
│ │ ├── SendWelcomeEmailOnUserCreated.php # Listens to UserCreatedByAdmin
│ │ └── InvalidateUserCacheOnUpdate.php
│ │
│ └── Infrastructure/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Api/
│ │ │ │ ├── AdminUserController.php # Full CRUD — Super Admin only
│ │ │ │ │ # GET /api/admin/users
│ │ │ │ │ # POST /api/admin/users
│ │ │ │ │ # GET /api/admin/users/{id}
│ │ │ │ │ # PUT /api/admin/users/{id}
│ │ │ │ │ # DELETE /api/admin/users/{id}
│ │ │ │ │ # POST /api/admin/users/{id}/suspend
│ │ │ │ │ # POST /api/admin/users/{id}/activate
│ │ │ │ │ # POST /api/admin/users/{id}/reset-password
│ │ │ │ └── UserProfileController.php # Authenticated user's own profile
│ │ │ └── Web/
│ │ │ └── ProfilePageController.php # Inertia
│ │ │
│ │ ├── Requests/
│ │ │ ├── CreateUserRequest.php
│ │ │ ├── UpdateUserRequest.php
│ │ │ └── UpdateProfileRequest.php
│ │ │
│ │ └── Resources/
│ │ ├── UserResource.php
│ │ ├── UserListResource.php
│ │ ├── UserProfileResource.php
│ │ └── UserCollection.php
│ │
│ ├── WebSocket/
│ │ ├── Channels/
│ │ │ └── UserPresenceChannel.php
│ │ └── Events/
│ │ └── UserStatusChangedBroadcast.php
│ │
│ ├── Persistence/
│ │ ├── Eloquent/
│ │ │ ├── Models/
│ │ │ │ ├── UserEloquentModel.php # @internal — Infrastructure only. Use UserRepositoryPort.
│ │ │ │ │ # HasRoles, HasApiTokens, SoftDeletes, setup_status
│ │ │ │ └── UserProfileEloquentModel.php # @internal
│ │ │ ├── Migrations/
│ │ │ │ ├── 2026_01_create_users_table.php # +setup_status, +setup_token_expires_at
│ │ │ │ └── 2026_02_create_user_profiles_table.php
│ │ │ ├── Seeders/
│ │ │ │ └── SuperAdminSeeder.php
│ │ │ └── Factories/
│ │ │ └── UserFactory.php
│ │ │
│ │ ├── Repositories/
│ │ │ ├── EloquentUserRepository.php # Implements UserRepositoryPort
│ │ │ └── EloquentUserProfileRepository.php
│ │ │
│ │ └── Mappers/
│ │ ├── UserMapper.php
│ │ └── UserProfileMapper.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ ├── ProcessAvatarJob.php # intervention/image
│ │ └── ResendSetupEmailJob.php
│ │
│ ├── Storage/
│ │ └── UserAvatarStorage.php # Wraps SpatieMediaLibraryAdapter for user avatars
│ │
│ └── Routes/
│ ├── api.php # /admin/users/\* — role:super-admin middleware
│ ├── web.php
│ └── channels.php
│
│
├── Notifications/ # 📧 MODULE: Notifications
│ │
│ ├── Providers/
│ │ └── NotificationsServiceProvider.php
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ └── Application/
│ │ │ └── SendNotificationHandlerTest.php
│ │ ├── Integration/
│ │ │ └── EloquentNotificationRepositoryTest.php
│ │ └── Feature/
│ │ ├── SendNotificationTest.php
│ │ └── RealtimeNotificationTest.php # WebSocket delivery
│ │
│ ├── Domain/
│ │ ├── Entities/
│ │ │ └── Notification.php
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── NotificationId.php # readonly
│ │ │ └── NotificationContent.php # readonly — title + body
│ │ │
│ │ ├── Enums/
│ │ │ ├── NotificationStatus.php # pending, sent, read, failed
│ │ │ └── DeliveryChannel.php # email, sms, push, in_app, websocket
│ │ │
│ │ ├── Ports/
│ │ │ └── NotificationRepositoryPort.php
│ │ │
│ │ └── Exceptions/
│ │ └── NotificationDeliveryException.php
│ │
│ ├── Application/
│ │ ├── Commands/
│ │ │ └── SendNotification/
│ │ │ ├── SendNotificationCommand.php
│ │ │ └── SendNotificationHandler.php
│ │ │
│ │ ├── Queries/
│ │ │ └── GetUserNotifications/
│ │ │ ├── GetUserNotificationsQuery.php
│ │ │ ├── GetUserNotificationsHandler.php
│ │ │ └── ReadModels/
│ │ │ └── NotificationReadModel.php
│ │ │
│ │ └── EventListeners/
│ │ └── BroadcastNotificationOnSent.php
│ │
│ └── Infrastructure/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ └── Api/
│ │ │ └── NotificationController.php
│ │ └── Resources/
│ │ ├── NotificationResource.php
│ │ └── NotificationCollection.php
│ │
│ ├── WebSocket/ # Real-time notifications (Reverb)
│ │ ├── Channels/
│ │ │ └── UserNotificationChannel.php
│ │ └── Events/
│ │ ├── NotificationSentBroadcast.php
│ │ └── NotificationReadBroadcast.php
│ │
│ ├── Persistence/
│ │ ├── Eloquent/
│ │ │ ├── Models/
│ │ │ │ └── NotificationEloquentModel.php # @internal
│ │ │ ├── Migrations/
│ │ │ │ └── 2026_01_create_notifications_table.php
│ │ │ └── Factories/
│ │ │ └── NotificationFactory.php
│ │ │
│ │ ├── Repositories/
│ │ │ └── EloquentNotificationRepository.php
│ │ │
│ │ └── Mappers/
│ │ └── NotificationMapper.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ ├── SendEmailNotificationJob.php
│ │ ├── SendSmsNotificationJob.php
│ │ └── SendPushNotificationJob.php
│ │
│ ├── ExternalServices/
│ │ ├── Push/
│ │ │ ├── FirebaseAdapter.php
│ │ │ └── OneSignalAdapter.php
│ │ └── Sms/
│ │ ├── SmsGatewayInterface.php
│ │ ├── TwilioAdapter.php
│ │ └── VonageAdapter.php
│ │
│ └── Routes/
│ ├── api.php
│ └── channels.php
│
│
├── AI/ # 🤖 MODULE: Artificial Intelligence
│ │
│ ├── Providers/
│ │ └── AIServiceProvider.php
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ └── Application/
│ │ │ └── GenerateContentHandlerTest.php
│ │ ├── Integration/
│ │ │ └── PrismLLMAdapterTest.php
│ │ └── Feature/
│ │ ├── GenerateContentTest.php
│ │ └── AIStreamingTest.php # WebSocket streaming
│ │
│ ├── Domain/
│ │ ├── Ports/
│ │ │ └── LLMProviderPort.php # Abstract interface — any LLM
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── Prompt.php # readonly
│ │ │ ├── AIResponse.php # readonly
│ │ │ └── StructuredSchema.php # readonly
│ │ │
│ │ ├── Enums/
│ │ │ ├── AIProvider.php # openai, anthropic, gemini, ollama
│ │ │ └── ResponseFormat.php # text, structured, stream
│ │ │
│ │ └── Services/
│ │ ├── ContentGenerator.php
│ │ └── TextAnalyzer.php
│ │
│ ├── Application/
│ │ ├── Commands/
│ │ │ ├── GenerateContent/
│ │ │ │ ├── GenerateContentCommand.php
│ │ │ │ └── GenerateContentHandler.php
│ │ │ └── AnalyzeText/
│ │ │ ├── AnalyzeTextCommand.php
│ │ │ └── AnalyzeTextHandler.php
│ │ │
│ │ └── Queries/
│ │ └── GetAIModels/
│ │ ├── GetAIModelsQuery.php
│ │ └── GetAIModelsHandler.php
│ │
│ └── Infrastructure/
│ ├── Http/
│ │ └── Controllers/
│ │ └── Api/
│ │ └── AIController.php
│ │
│ ├── WebSocket/ # Real-time AI streaming (Reverb)
│ │ └── Events/
│ │ └── AIResponseStreamBroadcast.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ ├── GenerateContentAsyncJob.php # Long-running AI generation
│ │ └── AnalyzeTextAsyncJob.php
│ │
│ ├── ExternalServices/
│ │ ├── Prism/ # prism-php/prism — primary LLM gateway
│ │ │ ├── PrismLLMAdapter.php # Text, chat, conversation
│ │ │ └── PrismStructuredAdapter.php # Structured output with DTOs
│ │ │
│ │ └── FastAPI/ # Optional: Python microservice for heavy ML
│ │ ├── FastAPIClientInterface.php
│ │ ├── FastAPIAdapter.php # Guzzle/Http — uses CircuitBreaker
│ │ ├── FastAPIHealthCheck.php
│ │ └── Config/
│ │ └── fastapi.php # base_url, timeout, retry, api_key
│ │
│ └── Routes/
│ ├── api.php
│ └── channels.php
│
│
└── {YourModule}/ # ⭐ TEMPLATE — Copy this for every new CRUD module
│
│ # ── HOW TO USE ────────────────────────────────────────────────────
│ # See step-by-step instructions at the top of this file.
│ # Replace: {YourModule} → e.g. Products
│ # {YourEntity} → e.g. Product
│ # {YourId} → e.g. ProductId
│ # Reference: Users module above is the complete example.
│ # ──────────────────────────────────────────────────────────────────
│
├── Providers/
│ └── {YourModule}ServiceProvider.php # ⚠️ REQUIRED: bind {YourEntity}RepositoryPort
│ # → Eloquent{YourEntity}Repository in register()
│
├── Tests/
│ ├── Unit/
│ │ ├── Domain/
│ │ │ └── {YourEntity}Test.php # Test domain invariants and business rules
│ │ └── Application/
│ │ ├── Create{YourEntity}HandlerTest.php
│ │ └── Update{YourEntity}HandlerTest.php
│ ├── Integration/
│ │ └── Eloquent{YourEntity}RepositoryTest.php # Tests DB round-trip via Mapper
│ └── Feature/
│ ├── {YourEntity}CrudTest.php # Full HTTP CRUD as authenticated user
│ └── {YourEntity}ExportTest.php # Excel + PDF export (if applicable)
│
├── Domain/ # ⭐ LAYER 1 — Pure, no framework imports
│ │
│ ├── Entities/
│ │ └── {YourEntity}.php # extends AggregateRoot
│ │ # Holds business logic + domain events
│ │ # NEVER import Eloquent here
│ │
│ ├── ValueObjects/
│ │ ├── {YourId}.php # readonly — ramsey/uuid
│ │ └── ... # Other domain VOs as needed
│ │
│ ├── Enums/
│ │ └── {YourEntity}Status.php # active, inactive, archived, deleted
│ │
│ ├── Ports/ # Repository interface — no implementation here
│ │ └── {YourEntity}RepositoryPort.php # find(), save(), delete(), list()
│ │
│ ├── Services/ # Domain services — stateless business logic
│ │ └── {YourEntity}DomainService.php # Only if logic spans multiple entities
│ │
│ ├── Events/ # Dispatched via Laravel Events
│ │ ├── {YourEntity}Created.php
│ │ ├── {YourEntity}Updated.php
│ │ └── {YourEntity}Deleted.php
│ │
│ ├── Specifications/ # Reusable business rule checks
│ │ └── {YourEntity}MustBeUnique.php
│ │
│ └── Exceptions/
│ ├── {YourEntity}NotFoundException.php
│ └── {YourEntity}AlreadyExistsException.php
│
├── Application/ # ⚙️ LAYER 2 — Use cases (thin orchestrators)
│ │
│ ├── Commands/ # Write operations
│ │ │ # Pattern: inject Repository port directly
│ │ │ # Extend TransactionalHandler if DB atomicity needed
│ │ ├── Create{YourEntity}/
│ │ │ ├── Create{YourEntity}Command.php
│ │ │ └── Create{YourEntity}Handler.php
│ │ ├── Update{YourEntity}/
│ │ │ ├── Update{YourEntity}Command.php
│ │ │ └── Update{YourEntity}Handler.php
│ │ └── Delete{YourEntity}/
│ │ ├── Delete{YourEntity}Command.php
│ │ └── Delete{YourEntity}Handler.php # Soft delete — sets deleted_at
│ │
│ ├── Queries/ # Read operations
│ │ │ # Pattern: Eloquent used directly here (no Mapper needed)
│ │ │ # Return ReadModels, not Domain Entities
│ │ ├── Get{YourEntity}List/
│ │ │ ├── Get{YourEntity}ListQuery.php # Accepts FilterDTO
│ │ │ ├── Get{YourEntity}ListHandler.php
│ │ │ └── ReadModels/
│ │ │ └── {YourEntity}ListReadModel.php
│ │ └── Get{YourEntity}/
│ │ ├── Get{YourEntity}Query.php
│ │ ├── Get{YourEntity}Handler.php
│ │ └── ReadModels/
│ │ └── {YourEntity}DetailReadModel.php
│ │
│ ├── DTOs/
│ │ ├── Create{YourEntity}DTO.php # spatie/laravel-data
│ │ ├── Update{YourEntity}DTO.php
│ │ └── {YourEntity}FilterDTO.php # page, perPage, search, dateFrom, dateTo, sortBy, sortDir
│ │
│ └── EventListeners/
│ └── On{YourEntity}Created.php # React to domain events (async)
│
└── Infrastructure/ # 🔌 LAYER 3 — Framework + adapters
│
├── Http/
│ ├── Controllers/
│ │ ├── Api/
│ │ │ ├── {YourEntity}Controller.php # index, show, store, update, destroy
│ │ │ └── {YourEntity}ExportController.php # GET /export?format=excel|pdf
│ │ └── Web/
│ │ └── {YourEntity}PageController.php # Inertia responses
│ │
│ ├── Requests/
│ │ ├── {YourEntity}FilterRequest.php # Validates FilterDTO fields
│ │ ├── Create{YourEntity}Request.php
│ │ └── Update{YourEntity}Request.php
│ │
│ └── Resources/
│ ├── {YourEntity}Resource.php
│ └── {YourEntity}Collection.php
│
├── WebSocket/ # Only if this module has real-time updates
│ ├── Channels/
│ │ └── {YourEntity}Channel.php
│ └── Events/
│ └── {YourEntity}UpdatedBroadcast.php
│
├── Persistence/
│ ├── Eloquent/
│ │ ├── Models/
│ │ │ └── {YourEntity}EloquentModel.php # ⚠️ ALWAYS add @internal docblock
│ │ │ # SoftDeletes + timestamps
│ │ ├── Migrations/
│ │ │ └── 2026_xx_create*{your_entities}\_table.php
│ │ ├── Seeders/
│ │ │ └── {YourEntity}Seeder.php
│ │ └── Factories/
│ │ └── {YourEntity}Factory.php
│ │
│ ├── Repositories/
│ │ └── Eloquent{YourEntity}Repository.php # Implements {YourEntity}RepositoryPort
│ │ # Uses {YourEntity}Mapper internally
│ │
│ └── Mappers/
│ └── {YourEntity}Mapper.php # Eloquent ↔ Domain Entity
│ # ONLY class allowed to import both
│
├── Queue/
│ └── Jobs/
│ └── Export{YourEntity}QueuedJob.php # For large exports >50MB
│
└── Routes/
├── api.php # GET /api/{your-module}/{your-entities}
│ # POST /api/{your-module}/{your-entities}
│ # GET /api/{your-module}/{your-entities}/{id}
│ # PUT /api/{your-module}/{your-entities}/{id}
│ # DELETE /api/{your-module}/{your-entities}/{id}
│ # GET /api/{your-module}/{your-entities}/export?format=excel|pdf
├── web.php # Inertia routes (if applicable)
└── channels.php # Reverb channels (if applicable)

---

🛡️ Architecture Enforcement

① @internal on ALL Eloquent Models
────────────────────────────────────
Every \*EloquentModel.php must carry this docblock.
PHPStan level 8 will error if any class outside Infrastructure imports it.

    /**
     * Eloquent persistence model.
     *
     * @internal Only for use within Infrastructure\Persistence.
     *           Outside this layer, always inject the Repository Port.
     */
    final class {YourEntity}EloquentModel extends Model
    {
        // ...
    }

② PHPStan Configuration (phpstan.neon — place at project root)
────────────────────────────────────────────────────────────────

    includes:
        - vendor/nunomaduro/larastan/extension.neon

    parameters:
        level: 8
        paths:
            - src
            - tests

        checkInternalClassCaseSensitivity: true
        internalTag: '@internal'

        excludePaths:
            - _ide_helper.php
            - _ide_helper_models.php

        reportUnmatchedIgnoredErrors: true
        checkMissingIterableValueType: true

With this config, any Handler, Controller or Service that imports
an @internal EloquentModel directly will fail CI before merge.

③ TransactionalHandler — DB transaction base class
────────────────────────────────────────────────────
Extend this in any Command Handler that needs atomic DB operations.

    // Shared/Application/Transactions/TransactionalHandler.php
    abstract class TransactionalHandler
    {
        public function __construct(
            private readonly DatabaseTransaction $transaction
        ) {}

        protected function runInTransaction(Closure $callback): mixed
        {
            return $this->transaction->run($callback);
        }
    }

    // Usage in a Command Handler:
    class CreateUserHandler extends TransactionalHandler
    {
        public function handle(CreateUserCommand $command): void
        {
            $this->runInTransaction(function () use ($command) {
                $user = new User(...);
                $this->userRepository->save($user);
                $this->profileRepository->save($user->profile());
            });
        }
    }

④ Mapper pattern — the only class that touches both worlds
────────────────────────────────────────────────────────────

    // Infrastructure/Persistence/Mappers/UserMapper.php
    final class UserMapper
    {
        public function toDomain(UserEloquentModel $model): User
        {
            return new User(
                id:     new UserId($model->id),
                email:  new UserEmail($model->email),
                status: UserStatus::from($model->status),
            );
        }

        public function toEloquent(User $user): UserEloquentModel
        {
            $model = UserEloquentModel::findOrNew($user->id()->value());
            $model->email  = $user->email()->value();
            $model->status = $user->status()->value;
            return $model;
        }
    }

    // Repository uses Mapper — never imports Domain Entity directly in queries
    final class EloquentUserRepository implements UserRepositoryPort
    {
        public function __construct(private readonly UserMapper $mapper) {}

        public function findById(UserId $id): ?User
        {
            $model = UserEloquentModel::find($id->value());
            return $model ? $this->mapper->toDomain($model) : null;
        }

        public function save(User $user): void
        {
            $this->mapper->toEloquent($user)->save();
        }
    }

⑤ CQRS without Bus — call handlers directly
─────────────────────────────────────────────
No CommandBus overhead. Inject the handler directly in the controller.

    // Infrastructure/Http/Controllers/Api/AdminUserController.php
    final class AdminUserController extends Controller
    {
        public function __construct(
            private readonly CreateUserHandler    $createUser,
            private readonly ListUsersHandler     $listUsers,
            private readonly SuspendUserHandler   $suspendUser,
        ) {}

        public function store(CreateUserRequest $request): JsonResponse
        {
            $this->createUser->handle(
                new CreateUserCommand(
                    name:  $request->validated('name'),
                    email: $request->validated('email'),
                    role:  $request->validated('role'),
                )
            );
            return response()->json(['message' => 'User created'], 201);
        }

        public function index(UserFilterRequest $request): JsonResponse
        {
            $result = $this->listUsers->handle(
                new ListUsersQuery(FilterDTO::from($request->validated()))
            );
            return UserCollection::make($result)->response();
        }
    }

⑥ Query side — Eloquent used directly (no Mapper needed)
──────────────────────────────────────────────────────────
Read operations do not need domain invariants — Eloquent is fine here.

    // Application/Queries/ListUsers/ListUsersHandler.php
    final class ListUsersHandler
    {
        public function handle(ListUsersQuery $query): LengthAwarePaginator
        {
            return UserEloquentModel::query()
                ->when($query->filter->search, fn($q) =>
                    $q->where('name', 'like', "%{$query->filter->search}%")
                      ->orWhere('email', 'like', "%{$query->filter->search}%")
                )
                ->when($query->filter->status, fn($q) =>
                    $q->where('status', $query->filter->status)
                )
                ->select(['id', 'name', 'email', 'status', 'created_at'])
                ->paginate($query->filter->perPage)
                ->through(fn($m) => UserListReadModel::fromEloquent($m));
        }
    }

⑦ Circuit Breaker — wrap external service calls
──────────────────────────────────────────────────
Use on FastAPI, payment gateways, or any unreliable external HTTP call.

    // Infrastructure/ExternalServices/FastAPI/FastAPIAdapter.php
    final class FastAPIAdapter implements FastAPIClientInterface
    {
        public function __construct(
            private readonly CircuitBreaker $circuitBreaker,
            private readonly RetryPolicy    $retryPolicy,
            private readonly Http           $http,
        ) {}

        public function predict(PredictionRequestDTO $dto): PredictionResponseDTO
        {
            return $this->circuitBreaker->call(
                fn() => $this->retryPolicy->execute(
                    fn() => $this->http->post('/predict', $dto->toArray())
                )
            );
        }
    }

---

📦 Composer Packages → Architecture Mapping

# Core framework

laravel/framework → All modules
laravel/sanctum → Auth/Infrastructure (HasApiTokens)
laravel/fortify → Auth/Application/Services/AuthenticationService.php
laravel/reverb → Shared/Infrastructure/Broadcasting/ReverbAdapter.php
laravel/horizon → Shared/Infrastructure/Queue/ (dashboard + worker management)

# Queue / messaging

vladimir-yuldashev/laravel-queue-rabbitmq → Shared/Infrastructure/Queue/RabbitMQAdapter.php

# Auth / permissions

spatie/laravel-permission → Auth/Application/Permissions/ + UserEloquentModel HasRoles
spatie/laravel-one-time-passwords → Auth/Domain/Services/OtpGenerator.php
laravel/socialite → Auth/Infrastructure/ExternalServices/OAuth/

# Domain / application

spatie/laravel-data → Shared/Application/DTOs/BaseDTO.php + all DTOs
ramsey/uuid → Shared/Domain/ValueObjects/Uuid.php

# Storage

league/flysystem-aws-s3-v3 → Shared/Infrastructure/Storage/S3StorageAdapter.php
spatie/laravel-medialibrary → Shared/Infrastructure/Storage/SpatieMediaLibraryAdapter.php
intervention/image → Users/Infrastructure/Queue/Jobs/ProcessAvatarJob.php

# Mail

resend/resend-laravel → Shared/Infrastructure/Mail/ResendAdapter.php

# Exports

maatwebsite/excel → Shared/Infrastructure/Export/ExcelAdapter.php
barryvdh/laravel-dompdf → Shared/Infrastructure/Export/PdfAdapter.php

# Observability

open-telemetry/opentelemetry-php → Shared/Infrastructure/Observability/Tracing/
open-telemetry/exporter-otlp → Shared/Infrastructure/Observability/Tracing/OpenTelemetryAdapter.php
monolog/monolog → Shared/Infrastructure/Logging/ (central bus + Processors)

# Audit

spatie/laravel-activitylog → Shared/Infrastructure/Audit/SpatieActivityLogAdapter.php

# AI

prism-php/prism → AI/Infrastructure/ExternalServices/Prism/

# Static analysis

nunomaduro/larastan → phpstan.neon (Laravel-aware rules)
phpstan/phpstan-strict-rules → phpstan.neon (strict mode)

# Testing

pestphp/pest → All modules Tests/
pestphp/pest-plugin-laravel → Feature tests
spatie/laravel-backup → Shared/Infrastructure/ (scheduled backups)

# Dev only

barryvdh/laravel-debugbar → Dev only (APP_DEBUG=true)
barryvdh/laravel-ide-helper → Dev only (\_ide_helper.php)

---

📐 Layer Rules — what each layer can and cannot import

    Layer           │ Can import                                    │ Cannot import
    ────────────────┼───────────────────────────────────────────────┼───────────────────────────────
    Domain          │ Shared/Domain, own VOs, own Enums             │ Eloquent, Laravel, HTTP, Queues
    Application     │ Domain, Shared/Application, DTOs              │ Eloquent (@internal enforced)
    Infrastructure  │ Domain (Ports), Application, Eloquent         │ No restrictions — it's the adapter
    ────────────────┴───────────────────────────────────────────────┴───────────────────────────────

    The Mapper is the ONLY class in the codebase allowed to import
    both the Domain Entity AND the EloquentModel simultaneously.
    No Controller, Handler, or Service should ever do this.

---

🔥 Key Differences vs Full Enterprise Architecture

    Feature                      │ This (Intermediate)        │ Full Enterprise
    ─────────────────────────────┼────────────────────────────┼───────────────────────────────
    CommandBus / QueryBus        │ ❌ Direct handler calls     │ ✅ Formal Bus with middleware
    IntegrationEvents            │ ❌ Laravel Events           │ ✅ Dedicated IntegrationEvent layer
    Anti-Corruption Layer (ACL)  │ ❌ Not needed for 2 devs   │ ✅ Between every context
    Contracts / Published Lang.  │ ❌ Not needed for 2 devs   │ ✅ Per context
    PHPArkitect tests            │ ❌ PHPStan is enough        │ ✅ CI layer boundary tests
    Domain Subscribers           │ ❌ Laravel Event Listeners  │ ✅ Synchronous domain subscribers
    Bounded Contexts             │ Modules (lighter)           │ Full DDD Bounded Contexts
    Team size                    │ 2 senior devs               │ 6-10+ devs
    ─────────────────────────────┴────────────────────────────┴───────────────────────────────

    When to upgrade to the full architecture:
    → Team grows to 4+ developers
    → Two devs need to work on the same module without conflicts
    → Modules have business rules that genuinely conflict
    → Parts of the system need to scale independently

── CRUD Route Pattern ────────────────────────────────────────────────────────

    Every CRUD module MUST register both Inertia page routes and API routes.
    All module routes live inside the authenticated middleware group in routes/web.php.
    Routes MUST use {uuid} (not {id}) to prevent enumeration attacks.

    Web (Inertia pages) — prefix: /{module}

        GET    /{module}                  → PageController@index    → {module}.index
        GET    /{module}/create           → PageController@create   → {module}.create
        GET    /{module}/{uuid}           → PageController@show     → {module}.show
        GET    /{module}/{uuid}/edit      → PageController@edit     → {module}.edit
        DELETE /{module}/{uuid}           → PageController@destroy  → {module}.destroy
        PATCH  /{module}/{uuid}/restore   → PageController@restore  → {module}.restore

    API — prefix: /api/{module}

        GET    /api/{module}              → Controller@index    → api.{module}.index
        GET    /api/{module}/{uuid}       → Controller@show     → api.{module}.show
        POST   /api/{module}              → Controller@store    → api.{module}.store
        PUT    /api/{module}/{uuid}       → Controller@update   → api.{module}.update
        DELETE /api/{module}/{uuid}       → Controller@destroy  → api.{module}.destroy
        PATCH  /api/{module}/{uuid}/restore → Controller@restore → api.{module}.restore

    All uuid parameters must use ->whereUuid('uuid').

── Permissions & Roles Convention ────────────────────────────────────────────

    For every CRUD module, generate 4 permissions + 1 role:

    Permissions:  VIEW_{MODULE}, CREATE_{MODULE}, UPDATE_{MODULE}, DELETE_{MODULE}
    Role:         {ModuleName} (PascalCase)
    Super Admin:  Always receives all module permissions.

    The permissions seeder MUST call:
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    BEFORE creating any permissions.

── SoftDeletes Convention ────────────────────────────────────────────────────

    Every CRUD module MUST include:
    1. SoftDeletes trait on EloquentModel (adds deleted_at column).
    2. softDeletes() in the migration.
    3. softDelete(string $uuid) + restore(string $uuid) on the Repository Port.
    4. DELETE + PATCH/restore routes in both web and API route groups.
    5. The delete handler performs soft-delete (->delete()), not hard-delete.

── Controller Convention ─────────────────────────────────────────────────────

    Controllers are orchestrators ONLY — they contain NO business logic.
    Pattern: inject CQRS handlers directly (no Bus in intermediate arch).

    Web Controllers (Inertia):  return Inertia::render('module/PageName', [...])
    API Controllers:            return response()->json([...])

    Every CRUD API Controller must have: index, show, store, update, destroy, restore
    Every CRUD Web Controller must have: index, create, show, edit, destroy, restore

── Eloquent Query Conventions ────────────────────────────────────────────────

    1. Always use select() — never SELECT *.
    2. Always use paginate() — never unbounded get() on listing queries.
    3. Use when() for conditional filters.
    4. Use scopeInDateRange() for date filtering.
    5. Query by uuid column, never by id, in all public-facing operations.

── Export Conventions (Excel & PDF) ──────────────────────────────────────────

    Every CRUD module MUST expose endpoints for Excel and PDF reporting.
    1. Excel: Uses maatwebsite/excel (implement FromQuery, WithHeadings, WithMapping, ShouldAutoSize).
    2. PDF: Uses barryvdh/laravel-dompdf (stylized template with Logo PNG.png).
    3. Both exports MUST reuse the same FilterDTO used for listing.
    4. Both exports MUST respect the current active filters (search, date range, etc.).
    5. Route pattern: GET /api/{module}/export?format=excel|pdf
    6. UI Requirement: Add ExportButton component to every index page.
