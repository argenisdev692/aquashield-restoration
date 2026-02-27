# PHP 8.5 · Laravel 12 · Enterprise Architecture 2026

src/
│
├── Core/ # 🔧 Cross-cutting concerns
│ │
│ ├── Shared/ # Shared code across contexts
│ │ │
│ │ ├── Domain/ # Shared domain
│ │ │ ├── Exceptions/ # Base domain exceptions
│ │ │ │ ├── DomainException.php
│ │ │ │ ├── ValidationException.php
│ │ │ │ ├── EntityNotFoundException.php
│ │ │ │ ├── UnauthorizedException.php
│ │ │ │ ├── BusinessRuleViolationException.php
│ │ │ │ ├── ConcurrencyException.php # 🔥 Optimistic locking / race conditions
│ │ │ │ ├── InvariantViolationException.php # 🔥 Aggregate invariant broken
│ │ │ │ └── IntegrationException.php # 🔥 Failure in external services
│ │ │ │
│ │ │ ├── ValueObjects/ # Value Objects (readonly + clone with PHP 8.5)
│ │ │ │ ├── Uuid.php # 🔥 readonly + ramsey/uuid + clone with
│ │ │ │ ├── Email.php # 🧬 readonly + clone with
│ │ │ │ ├── PhoneNumber.php # 🧬 readonly + clone with
│ │ │ │ ├── Timestamp.php # 🧬 readonly + clone with
│ │ │ │ ├── Money.php # 🧬 readonly + clone with
│ │ │ │ ├── Url.php # 🧬 readonly + PHP 8.5 Uri extension
│ │ │ │ └── DateRange.php # 🧬 readonly + clone with
│ │ │ │
│ │ │ ├── Entities/ # Base entities
│ │ │ │ └── AggregateRoot.php # 🧬 #[Override] support for child properties
│ │ │ │
│ │ │ ├── Ports/ # 🔥 Shared interfaces (ports)
│ │ │ │ ├── CachePort.php
│ │ │ │ ├── QueuePort.php
│ │ │ │ ├── LoggerPort.php
│ │ │ │ ├── EventPublisherPort.php
│ │ │ │ ├── NotificationPort.php
│ │ │ │ ├── AuditPort.php # 🔥 Audit (spatie/activitylog)
│ │ │ │ └── ExportPort.php # 🔥 Export/Import (maatwebsite/excel + barryvdh/laravel-dompdf)
│ │ │ │
│ │ │ └── Events/ # Event system
│ │ │ ├── DomainEvent.php
│ │ │ ├── DomainEventPublisher.php
│ │ │ └── DomainEventSubscriber.php
│ │ │
│ │ ├── Application/ # Shared application
│ │ │ │
│ │ │ ├── Bus/ # CQRS Bus
│ │ │ │ ├── Command/
│ │ │ │ │ ├── CommandBus.php
│ │ │ │ │ ├── CommandBusInterface.php
│ │ │ │ │ ├── CommandHandler.php
│ │ │ │ │ └── SyncCommandBus.php
│ │ │ │ │
│ │ │ │ ├── Query/
│ │ │ │ │ ├── QueryBus.php
│ │ │ │ │ ├── QueryBusInterface.php
│ │ │ │ │ ├── QueryHandler.php
│ │ │ │ │ └── SyncQueryBus.php
│ │ │ │ │
│ │ │ │ └── Event/
│ │ │ │ ├── EventBus.php
│ │ │ │ └── EventBusInterface.php
│ │ │ │
│ │ │ ├── DTOs/ # Base DTOs (spatie/laravel-data + PHP 8.5)
│ │ │ │ ├── BaseDTO.php # 🧬 extends Data + closures in constant expressions
│ │ │ │ ├── PaginationDTO.php # 🧬 readonly + clone with
│ │ │ │ └── FilterDTO.php # 🧬 readonly + clone with
│ │ │ │
│ │ │ ├── IntegrationEvents/ # 🔥 Events BETWEEN contexts (ACL)
│ │ │ │ ├── IntegrationEvent.php # Base class for integration events
│ │ │ │ └── IntegrationEventBus.php # Bus that communicates between bounded contexts
│ │ │ │
│ │ │ ├── ACL/ # 🔥 Anti-Corruption Layer
│ │ │ │ └── AntiCorruptionLayerInterface.php # Translates models between contexts
│ │ │ │
│ │ │ └── Services/ # Shared application services
│ │ │ ├── Pagination/
│ │ │ │ └── PaginationService.php
│ │ │ └── Validation/
│ │ │ └── ValidationService.php
│ │ │
│ │ └── Infrastructure/ # Shared infrastructure
│ │ │
│ │ ├── Cache/ # Cache system
│ │ │ ├── CacheInterface.php
│ │ │ ├── RedisAdapter.php
│ │ │ ├── MemcachedAdapter.php
│ │ │ └── InMemoryCacheAdapter.php
│ │ │
│ │ ├── Queue/ # Queue system (uses laravel/horizon)
│ │ │ ├── QueueInterface.php
│ │ │ ├── LaravelQueueAdapter.php
│ │ │ ├── RabbitMQAdapter.php
│ │ │ └── SqsAdapter.php
│ │ │
│ │ ├── Broadcasting/ # 🔥 Laravel Reverb / WebSockets
│ │ │ ├── BroadcastingInterface.php
│ │ │ ├── ReverbAdapter.php # 🔥 laravel/reverb
│ │ │ ├── PusherAdapter.php
│ │ │ ├── AblyAdapter.php
│ │ │ └── Config/
│ │ │ └── broadcasting.php
│ │ │
│ │ ├── Logging/ # Logging system — Monolog as central bus
│ │ │ ├── LoggerInterface.php
│ │ │ ├── ApplicationLogger.php
│ │ │ ├── MonologAdapter.php
│ │ │ ├── Handlers/
│ │ │ │ ├── OpenTelemetryMonologHandler.php # 🔥 Monolog → OTEL Collector (OTLP/gRPC)
│ │ │ │ └── StructuredJsonHandler.php # 🔥 Structured stdout for k8s/cloud
│ │ │ ├── Processors/
│ │ │ │ ├── TraceContextProcessor.php # 🔥 Injects trace_id + span_id into every log
│ │ │ │ ├── CorrelationIdProcessor.php # 🔥 Injects correlation_id from the request
│ │ │ │ └── RequestContextProcessor.php # 🔥 Injects user_id, tenant, route
│ │ │ └── Formatters/
│ │ │ ├── JsonFormatter.php
│ │ │ └── ContextualFormatter.php
│ │ │
│ │ ├── Observability/ # Metrics & Health & Tracing
│ │ │ ├── HealthCheck/
│ │ │ │ ├── HealthCheckInterface.php
│ │ │ │ ├── HealthCheckController.php # 🔥 GET /health → aggregated status
│ │ │ │ ├── HealthCheckAggregator.php # 🔥 Runs all health checks
│ │ │ │ ├── DatabaseHealthCheck.php
│ │ │ │ ├── RedisHealthCheck.php
│ │ │ │ ├── QueueHealthCheck.php
│ │ │ │ ├── ReverbHealthCheck.php # 🔥 Health check for Reverb
│ │ │ │ ├── ExternalServiceHealthCheck.php # 🔥 Resend, S3, Stripe, etc.
│ │ │ │ └── StorageHealthCheck.php # 🔥 S3 / local disk
│ │ │ │
│ │ │ ├── Metrics/
│ │ │ │ ├── MetricsInterface.php
│ │ │ │ ├── PrometheusAdapter.php # 🔥 Prometheus pull model
│ │ │ │ ├── PrometheusController.php # 🔥 GET /metrics → Prometheus format
│ │ │ │ └── StatsDAdapter.php
│ │ │ │
│ │ │ ├── Tracing/ # 🔥 Distributed Tracing — OTEL SDK 1.x
│ │ │ │ ├── TracingInterface.php
│ │ │ │ ├── OpenTelemetryAdapter.php # 🔥 OTEL 2026 standard (CNCF Graduated)
│ │ │ │ ├── OpenTelemetryConfig.php # 🔥 Config: OTLP exporter, sampler, resource
│ │ │ │ ├── OtlpGrpcExporter.php # 🔥 OTLP/gRPC → Collector (Jaeger/Zipkin receive via OTLP)
│ │ │ │ ├── InstrumentationProvider.php # 🔥 Registers auto-instrumentation: SQL, HTTP, Redis
│ │ │ │ └── SpanEnricher.php # 🔥 Adds context to spans
│ │ │ │
│ │ │ └── Logging/ # 🔥 Structured Logging — bridge OTEL ↔ Monolog
│ │ │ └── OtelMonologBridge.php # 🔥 Forwards Monolog logs to OTEL Collector via OTLP
│ │ │
│ │ ├── Resilience/ # Resilience
│ │ │ ├── CircuitBreaker/
│ │ │ │ ├── CircuitBreaker.php
│ │ │ │ ├── CircuitBreakerInterface.php
│ │ │ │ ├── CircuitBreakerState.php
│ │ │ │ └── CircuitBreakerMetricsExporter.php # 🔥 Exports state → Prometheus (open/closed/half-open)
│ │ │ │
│ │ │ ├── RateLimiter/
│ │ │ │ ├── RateLimiter.php
│ │ │ │ └── RateLimiterInterface.php
│ │ │ │
│ │ │ └── Retry/
│ │ │ ├── RetryPolicy.php
│ │ │ └── ExponentialBackoff.php
│ │ │
│ │ ├── Persistence/ # Shared persistence
│ │ │ ├── Database/
│ │ │ │ ├── Connection.php
│ │ │ │ └── QueryBuilder.php
│ │ │ │
│ │ │ └── Transactions/ # 🔥 DB Transactions & Rollback
│ │ │ ├── TransactionInterface.php
│ │ │ ├── DatabaseTransaction.php
│ │ │ ├── UnitOfWork.php # 🔥 Coordinates persist + flush + rollback
│ │ │ ├── TransactionalCommandBus.php # 🔥 Wraps CommandBus in DB::transaction
│ │ │ └── RollbackStrategy.php # 🔥 Rollback strategy (auto/manual/saga)
│ │ │
│ │ ├── EventStore/ # Event Sourcing (optional)
│ │ │ ├── EventStore.php
│ │ │ └── EventStoreInterface.php
│ │ │
│ │ ├── Audit/ # 🔥 Cross-cutting audit (spatie/activitylog)
│ │ │ ├── AuditInterface.php
│ │ │ ├── SpatieActivityLogAdapter.php # Wraps spatie/laravel-activitylog
│ │ │ └── AuditableInterface.php # Contract for auditable models
│ │ │
│ │ └── Export/ # 🔥 Cross-cutting Export/Import (maatwebsite/excel + barryvdh/laravel-dompdf)
│ │ ├── ExportInterface.php
│ │ ├── ExcelAdapter.php # 🔥 Wraps maatwebsite/excel (FromQuery, WithHeadings, WithMapping, ShouldAutoSize)
│ │ ├── PdfAdapter.php # 🔥 Wraps barryvdh/laravel-dompdf v3 — Pdf::loadView() + setPaper() + stream()
│ │ ├── PdfTemplateRenderer.php # 🔥 Resolves Blade view path, injects $filters $generatedAt $rows
│ │ └── CsvAdapter.php
│ │
│ ├── Middleware/ # Global middleware
│ │ ├── AuthenticationMiddleware.php
│ │ ├── AuthorizationMiddleware.php # 🔥 Authorize via Gates/Policies
│ │ ├── LoggingMiddleware.php
│ │ ├── CorrelationIdMiddleware.php
│ │ ├── TraceContextMiddleware.php # 🔥 Propagates OpenTelemetry trace_id
│ │ ├── RateLimitMiddleware.php
│ │ ├── CookieConsentMiddleware.php # 🔥 spatie/laravel-cookie-consent
│ │ └── SecurityHeadersMiddleware.php
│ │
│ ├── Pipes/ # Validation Pipes (🧬 PHP 8.5 pipe operator |>)
│ │ ├── ValidationPipe.php # 🧬 Uses pipe operator |> internally
│ │ ├── TransformationPipe.php # 🧬 Uses pipe operator |> internally
│ │ └── SanitizationPipe.php # 🧬 Uses pipe operator |> internally
│ │
│ ├── Filters/ # Exception Filters
│ │ ├── GlobalExceptionFilter.php
│ │ ├── ValidationExceptionFilter.php
│ │ ├── DomainExceptionFilter.php
│ │ └── HttpExceptionFilter.php
│ │
│ └── Providers/ # Core Service Providers
│ ├── CoreServiceProvider.php
│ ├── EventServiceProvider.php
│ ├── BusServiceProvider.php
│ ├── BroadcastingServiceProvider.php # 🔥 Reverb Provider
│ ├── AuditServiceProvider.php # 🔥 spatie/activitylog config
│ └── ExportServiceProvider.php # 🔥 maatwebsite/excel + barryvdh/laravel-dompdf config

└── Contexts/ # 📦 Bounded Contexts (Modules)
│
├── Auth/ # 🔐 CONTEXT: Authentication
│ │
│ ├── Providers/ # Context Service Providers
│ │ └── AuthServiceProvider.php
│ │
│ ├── Config/ # Context configuration
│ │ └── auth.php
│ │
│ ├── Contracts/ # 🔥 Published Language (context public API)
│ │ ├── AuthServiceContract.php # What other contexts can consume
│ │ └── DTOs/
│ │ ├── AuthenticatedUserDTO.php # Output DTO for other contexts
│ │ └── TokenValidationResultDTO.php
│ │
│ ├── Tests/ # Full context tests
│ │ ├── Unit/
│ │ │ ├── Domain/
│ │ │ └── Application/
│ │ │
│ │ ├── Integration/
│ │ │ └── Infrastructure/
│ │ │
│ │ └── Feature/
│ │ ├── OtpFlowTest.php
│ │ └── RealtimeAuthTest.php # 🔥 WebSocket tests
│ │
│ ├── Domain/ # ⭐ LAYER 1: Pure Domain
│ │ │
│ │ ├── Entities/ # Domain entities (🧬 #[Override] PHP 8.5)
│ │ │ ├── User.php # 🧬 #[Override] $id: UserId
│ │ │ ├── Session.php # 🧬 #[Override] $id: SessionId
│ │ │ ├── RefreshToken.php # 🧬 #[Override] $id
│ │ │ └── ApiToken.php # 🧬 #[Override] $id
│ │ │
│ │ ├── ValueObjects/ # Value Objects (🧬 readonly + clone with PHP 8.5)
│ │ │ ├── OtpCode.php # 🧬 readonly + clone with
│ │ │ ├── PhoneNumber.php # 🧬 readonly + clone with
│ │ │ ├── UserEmail.php # 🧬 readonly + clone with
│ │ │ ├── UserId.php # 🧬 readonly + clone with
│ │ │ ├── SessionId.php # 🧬 readonly + clone with
│ │ │ ├── Password.php # 🧬 readonly + clone with
│ │ │ ├── HashedPassword.php # 🧬 readonly + clone with
│ │ │ ├── SetupToken.php # 🔥 First-access / admin reset token (signed URL token)
│ │ │ └── IpAddress.php # 🧬 readonly + clone with
│ │ │
│ │ ├── Enums/ # 🧬 PHP 8.5 Backed Enums (domain state)
│ │ │ ├── OtpStatus.php # pending, verified, expired, revoked
│ │ │ ├── SessionStatus.php # active, expired, revoked
│ │ │ ├── UserSetupStatus.php # 🔥 pending_setup, active — tracks whether user has set their password
│ │ │ ├── AuthProvider.php # email, google, github, microsoft
│ │ │ └── TokenType.php # access, refresh, api, setup 🔥 (+setup for first-time access)
│ │ │
│ │ ├── Ports/ # 🔥 Interfaces (Ports)
│ │ │ ├── OtpRepositoryPort.php
│ │ │ ├── UserRepositoryPort.php
│ │ │ ├── SessionRepositoryPort.php
│ │ │ └── RefreshTokenRepositoryPort.php
│ │ │
│ │ ├── Services/ # Domain Services (🧬 #[NoDiscard] PHP 8.5)
│ │ │ ├── OtpGenerator.php # 🔥 spatie OTP + 🧬 #[NoDiscard]
│ │ │ ├── OtpValidator.php # 🧬 #[NoDiscard] — result must never be ignored
│ │ │ ├── PasswordHasher.php # 🧬 #[NoDiscard]
│ │ │ ├── TokenGenerator.php # 🧬 #[NoDiscard]
│ │ │ └── SessionManager.php # 🧬 #[NoDiscard]
│ │ │
│ │ ├── Policies/ # 🔥 Domain Policies (domain authorization)
│ │ │ ├── CanLoginPolicy.php
│ │ │ ├── CanResetPasswordPolicy.php
│ │ │ └── MaxOtpAttemptsPolicy.php
│ │ │
│ │ ├── Authorization/ # 🔥 Authorization Layer (Gates + Guards)
│ │ │ ├── Gates/
│ │ │ │ ├── ManageUsersGate.php # 🔥 Gate::define('manage-users')
│ │ │ │ ├── ManageRolesGate.php
│ │ │ │ └── ViewAuditLogGate.php
│ │ │ │
│ │ │ ├── Guards/
│ │ │ │ ├── SanctumGuard.php # 🔥 API token guard (laravel/sanctum)
│ │ │ │ ├── SessionGuard.php # 🔥 Web session guard (fortify)
│ │ │ │ └── OtpGuard.php # 🔥 OTP-based auth guard
│ │ │ │
│ │ │ └── LaravelPolicies/ # 🔥 Laravel Policy classes
│ │ │ ├── UserPolicy.php # 🔥 $this->authorize('update', $user)
│ │ │ ├── SessionPolicy.php
│ │ │ └── TokenPolicy.php
│ │ │
│ │ ├── Events/ # Domain Events
│ │ │ ├── OtpGenerated.php
│ │ │ ├── OtpVerified.php
│ │ │ ├── OtpExpired.php
│ │ │ ├── UserRegistered.php
│ │ │ ├── UserLoggedIn.php
│ │ │ ├── UserLoggedOut.php
│ │ │ ├── PasswordChanged.php
│ │ │ ├── FirstTimePasswordSet.php # 🔥 User completed initial password setup
│ │ │ ├── PasswordResetByAdmin.php # 🔥 Super Admin forced a password reset
│ │ │ └── SessionExpired.php
│ │ │
│ │ ├── Subscribers/ # 🔥 Domain Event Subscribers (synchronous, same transaction, MUST perform Cache::forget)
│ │ │ ├── InvalidateSessionOnPasswordChange.php
│ │ │ └── RevokeTokensOnLogout.php
│ │ │
│ │ ├── Exceptions/ # Domain Exceptions
│ │ │ ├── InvalidOtpException.php
│ │ │ ├── ExpiredOtpException.php
│ │ │ ├── OtpAlreadyUsedException.php
│ │ │ ├── InvalidCredentialsException.php
│ │ │ ├── UserAlreadyExistsException.php
│ │ │ ├── UserNotFoundException.php
│ │ │ ├── SessionExpiredException.php
│ │ │ ├── InvalidTokenException.php
│ │ │ ├── SetupTokenExpiredException.php # 🔥 First-access link has expired
│ │ │ └── PasswordAlreadySetException.php # 🔥 Attempt to reuse first-access link
│ │ │
│ │ └── Specifications/ # Specification Pattern (🧬 #[NoDiscard] PHP 8.5)
│ │ ├── UserMustHaveUniqueEmail.php # 🧬 #[NoDiscard] on isSatisfiedBy()
│ │ └── OtpMustNotBeExpired.php # 🧬 #[NoDiscard] on isSatisfiedBy()
│ │
│ ├── Application/ # ⚙️ LAYER 2: Use Cases
│ │ │
│ │ ├── Commands/ # CQRS - Write Operations (🔥 MUST dispatch Domain Events for mutation)
│ │ │ │
│ │ │ ├── SendOtp/
│ │ │ │ ├── SendOtpCommand.php
│ │ │ │ └── SendOtpHandler.php
│ │ │ │
│ │ │ ├── VerifyOtp/
│ │ │ │ ├── VerifyOtpCommand.php
│ │ │ │ └── VerifyOtpHandler.php
│ │ │ │
│ │ │ ├── RegisterUser/
│ │ │ │ ├── RegisterUserCommand.php
│ │ │ │ └── RegisterUserHandler.php
│ │ │ │
│ │ │ ├── Login/
│ │ │ │ ├── LoginCommand.php
│ │ │ │ └── LoginHandler.php
│ │ │ │
│ │ │ ├── LoginWithOtp/
│ │ │ │ ├── LoginWithOtpCommand.php
│ │ │ │ └── LoginWithOtpHandler.php
│ │ │ │
│ │ │ ├── Logout/
│ │ │ │ ├── LogoutCommand.php
│ │ │ │ └── LogoutHandler.php
│ │ │ │
│ │ │ ├── RefreshToken/
│ │ │ │ ├── RefreshTokenCommand.php
│ │ │ │ └── RefreshTokenHandler.php
│ │ │ │
│ │ │ ├── ChangePassword/
│ │ │ │ ├── ChangePasswordCommand.php
│ │ │ │ └── ChangePasswordHandler.php
│ │ │ │
│ │ │ ├── ResetPassword/
│ │ │ │ ├── RequestPasswordResetCommand.php
│ │ │ │ ├── RequestPasswordResetHandler.php
│ │ │ │ ├── ResetPasswordCommand.php
│ │ │ │ └── ResetPasswordHandler.php
│ │ │ │
│ │ │ ├── SetFirstTimePassword/ # 🔥 User follows link and sets their password for the first time
│ │ │ │ ├── SetFirstTimePasswordCommand.php
│ │ │ │ └── SetFirstTimePasswordHandler.php
│ │ │ │
│ │ │ └── RevokeSession/
│ │ │ ├── RevokeSessionCommand.php
│ │ │ └── RevokeSessionHandler.php
│ │ │
│ │ ├── Queries/ # CQRS - Read Operations (🔥 MUST implement Cache::remember with TTL)
│ │ │ │
│ │ │ ├── GetUser/
│ │ │ │ ├── GetUserQuery.php
│ │ │ │ └── GetUserHandler.php
│ │ │ │
│ │ │ ├── GetUserByEmail/
│ │ │ │ ├── GetUserByEmailQuery.php
│ │ │ │ └── GetUserByEmailHandler.php
│ │ │ │
│ │ │ ├── GetActiveSessions/
│ │ │ │ ├── GetActiveSessionsQuery.php
│ │ │ │ └── GetActiveSessionsHandler.php
│ │ │ │
│ │ │ ├── VerifyToken/
│ │ │ │ ├── VerifyTokenQuery.php
│ │ │ │ └── VerifyTokenHandler.php
│ │ │ │
│ │ │ ├── GetUserPermissions/
│ │ │ │ ├── GetUserPermissionsQuery.php
│ │ │ │ └── GetUserPermissionsHandler.php
│ │ │ │
│ │ │ └── ReadModels/ # 🔥 CQRS Read Models (moved from Infrastructure)
│ │ │ ├── UserReadModel.php
│ │ │ └── SessionReadModel.php
│ │ │
│ │ ├── EventHandlers/ # Event Handlers (async, post-commit)
│ │ │ ├── OnOtpGenerated.php
│ │ │ ├── OnOtpVerified.php
│ │ │ ├── OnUserRegistered.php
│ │ │ ├── OnUserLoggedIn.php
│ │ │ ├── OnPasswordChanged.php
│ │ │ └── OnSessionExpired.php
│ │ │
│ │ ├── IntegrationEvents/ # 🔥 Events this context publishes to others
│ │ │ ├── UserRegisteredIntegration.php
│ │ │ ├── UserAuthenticatedIntegration.php
│ │ │ └── PasswordChangedIntegration.php
│ │ │
│ │ ├── ACL/ # 🔥 Anti-Corruption Layer
│ │ │ └── UserFromNotificationsACL.php # Translates models from Notifications → Auth
│ │ │
│ │ ├── Services/ # Application Services (thin orchestrators)
│ │ │ ├── AuthenticationService.php # 🔥 Orchestrates Fortify + Sanctum
│ │ │ ├── PermissionService.php # 🔥 Wraps spatie/laravel-permission
│ │ │ └── AuthorizationService.php # 🔥 Evaluates Gates + Policies in Application layer
│ │ │
│ │ └── Permissions/ # 🔥 Permission Management (spatie/permission)
│ │ ├── SyncPermissionsCommand.php # Sync DB permissions with code
│ │ ├── RoleDefinitions.php # Declarative list of system roles
│ │ └── PermissionDefinitions.php # Declarative list of system permissions
│ │
│ └── Infrastructure/ # 🔌 LAYER 3: Adapters
│ │
│ ├── Adapters/ # 🔥 All external adapters
│ │ │
│ │ ├── Http/ # REST API Adapter
│ │ │ │
│ │ │ ├── Controllers/
│ │ │ │ ├── Api/ # API Controllers
│ │ │ │ │ ├── AuthController.php
│ │ │ │ │ ├── OtpController.php
│ │ │ │ │ ├── SessionController.php
│ │ │ │ │ ├── SetupPasswordController.php # 🔥 GET+POST /auth/setup-password/{token} — first access & admin reset
│ │ │ │ │ └── UserController.php
│ │ │ │ │
│ │ │ │ └── Web/ # Inertia Controllers
│ │ │ │ ├── LoginPageController.php
│ │ │ │ ├── RegisterPageController.php
│ │ │ │ ├── DashboardController.php
│ │ │ │ └── ProfileController.php
│ │ │ │
│ │ │ ├── Requests/ # Form Requests (Validation)
│ │ │ │ ├── SendOtpRequest.php
│ │ │ │ ├── VerifyOtpRequest.php
│ │ │ │ ├── LoginRequest.php
│ │ │ │ ├── RegisterRequest.php
│ │ │ │ ├── ChangePasswordRequest.php
│ │ │ │ ├── ResetPasswordRequest.php
│ │ │ │ └── SetFirstTimePasswordRequest.php # 🔥 Password validation for first-time access / admin reset
│ │ │ │
│ │ │ ├── Resources/ # API Resources (JSON Serialization)
│ │ │ │ ├── UserResource.php
│ │ │ │ ├── SessionResource.php
│ │ │ │ ├── UserCollection.php
│ │ │ │ └── PermissionResource.php
│ │ │ │
│ │ │ ├── Middleware/ # Context-specific Middleware
│ │ │ │ ├── OtpRateLimitMiddleware.php
│ │ │ │ ├── EnsureEmailIsVerified.php
│ │ │ │ └── CheckSessionValidity.php
│ │ │ │
│ │ │ └── Responses/ # HTTP Responses
│ │ │ ├── AuthSuccessResponse.php
│ │ │ └── OtpSentResponse.php
│ │ │
│ │ ├── WebSocket/ # 🔥 WebSocket Adapter (Reverb)
│ │ │ │
│ │ │ ├── Channels/ # WebSocket Channels
│ │ │ │ ├── UserChannel.php
│ │ │ │ ├── SessionChannel.php
│ │ │ │ └── PresenceChannel.php
│ │ │ │
│ │ │ ├── Events/ # Broadcast Events
│ │ │ │ ├── UserLoggedInBroadcast.php
│ │ │ │ ├── SessionExpiredBroadcast.php
│ │ │ │ ├── OtpSentBroadcast.php
│ │ │ │ └── NewDeviceLoginBroadcast.php
│ │ │ │
│ │ │ ├── Listeners/ # WebSocket Event Listeners
│ │ │ │ ├── BroadcastUserLogin.php
│ │ │ │ ├── BroadcastSessionExpiry.php
│ │ │ │ └── BroadcastOtpSent.php
│ │ │ │
│ │ │ ├── Middleware/
│ │ │ │ └── AuthorizeWebSocketMiddleware.php
│ │ │ │
│ │ │ └── Config/
│ │ │ └── channels.php # Channel definitions
│ │ │
│ │ └── CLI/ # CLI Adapter (Artisan Commands)
│ │ │
│ │ ├── Commands/
│ │ │ ├── CleanExpiredOtpCommand.php
│ │ │ ├── CleanExpiredSessionsCommand.php
│ │ │ ├── GenerateApiTokenCommand.php
│ │ │ └── Maintenance/
│ │ │ ├── ClearAllCachesCommand.php
│ │ │ ├── OptimizeProductionCommand.php
│ │ │ └── WarmUpCacheCommand.php
│ │ │
│ │ └── Kernel/
│ │ └── AuthConsoleKernel.php
│ │
│ ├── Persistence/ # Persistence
│ │ │
│ │ ├── Eloquent/ # Eloquent ORM
│ │ │ │
│ │ │ ├── Models/ # Eloquent Models (🔥 MUST use Spatie\Activitylog\Traits\LogsActivity)
│ │ │ │ ├── OtpEloquentModel.php # @internal — Infrastructure only. Use OtpRepositoryPort.
│ │ │ │ ├── UserEloquentModel.php # @internal — Infrastructure only. Use UserRepositoryPort.
│ │ │ │ │ # HasRoles (spatie/permission), HasApiTokens (sanctum)
│ │ │ │ ├── SessionEloquentModel.php # @internal — Infrastructure only. Use SessionRepositoryPort.
│ │ │ │ └── RefreshTokenEloquentModel.php # @internal — Infrastructure only.
│ │ │ │
│ │ │ ├── Migrations/ # Database Migrations
│ │ │ │ ├── 2024_01_create_otp_codes_table.php
│ │ │ │ ├── 2024_02_create_users_table.php
│ │ │ │ ├── 2024_03_create_sessions_table.php
│ │ │ │ └── 2024_04_create_refresh_tokens_table.php
│ │ │ │
│ │ │ ├── Seeders/ # Database Seeders
│ │ │ │ ├── RolesSeeder.php # 🔥 Uses spatie/laravel-permission
│ │ │ │ ├── PermissionsSeeder.php # 🔥 Uses spatie/laravel-permission
│ │ │ │ └── DefaultUsersSeeder.php
│ │ │ │
│ │ │ └── Factories/ # Model Factories (PEST tests)
│ │ │ ├── UserFactory.php
│ │ │ └── OtpFactory.php
│ │ │
│ │ ├── Repositories/ # Repository Implementations
│ │ │ ├── EloquentOtpRepository.php
│ │ │ ├── EloquentUserRepository.php
│ │ │ ├── EloquentSessionRepository.php
│ │ │ └── EloquentRefreshTokenRepository.php
│ │ │
│ │ └── Mappers/ # 🔥 Eloquent ↔ Domain Mappers
│ │ ├── OtpMapper.php
│ │ ├── UserMapper.php
│ │ ├── SessionMapper.php
│ │ └── RefreshTokenMapper.php
│ │ # 🔥 ReadModels moved to Application/Queries/ReadModels/ (pure CQRS)
│ │
│ ├── Queue/ # 🔥 Queue Jobs (separate from adapters)
│ │ ├── Jobs/
│ │ │ ├── SendOtpSmsJob.php
│ │ │ ├── SendOtpEmailJob.php
│ │ │ ├── CleanExpiredOtpJob.php
│ │ │ ├── CleanExpiredSessionsJob.php
│ │ │ ├── SendPasswordResetEmailJob.php
│ │ │ ├── SendWelcomeSetupEmailJob.php # 🔥 Welcome email with first-access link (registration)
│ │ │ └── SendAdminPasswordResetEmailJob.php # 🔥 Email sent when Super Admin resets a password
│ │ │
│ │ └── Handlers/ # Application-level handlers
│ │ ├── SendOtpSmsHandler.php
│ │ └── SendOtpEmailHandler.php
│ │
│ ├── ExternalServices/ # External Services Adapters
│ │ │
│ │ ├── Notifications/ # Notification Services
│ │ │ │
│ │ │ ├── SMS/
│ │ │ │ ├── SmsGatewayInterface.php
│ │ │ │ ├── TwilioAdapter.php
│ │ │ │ ├── VonageAdapter.php # 🔥 Integrates with spatie OTP via SMS
│ │ │ │ ├── MessageBirdAdapter.php
│ │ │ │ └── AwsSnsAdapter.php
│ │ │ │
│ │ │ └── Email/
│ │ │ ├── EmailServiceInterface.php
│ │ │ ├── MailgunAdapter.php
│ │ │ ├── SendGridAdapter.php
│ │ │ ├── SesAdapter.php
│ │ │ ├── ResendAdapter.php # 🔥 resend/resend-laravel
│ │ │ └── ReactEmailTemplateRenderer.php # 🔥 React Email templates (optional)
│ │ │
│ │ ├── OAuth/ # OAuth Providers (laravel/socialite)
│ │ │ ├── OAuthProviderInterface.php
│ │ │ ├── GoogleOAuthAdapter.php
│ │ │ ├── FacebookOAuthAdapter.php
│ │ │ ├── GithubOAuthAdapter.php
│ │ │ └── MicrosoftOAuthAdapter.php
│ │ │
│ │ ├── Analytics/ # Analytics Services
│ │ │ ├── AnalyticsInterface.php
│ │ │ ├── GoogleAnalyticsAdapter.php
│ │ │ └── MixpanelAdapter.php
│ │ │
│ │ └── Security/ # Security Services
│ │ ├── IpGeolocation/
│ │ │ ├── IpGeolocationInterface.php
│ │ │ └── IpApiAdapter.php
│ │ │
│ │ └── FraudDetection/
│ │ ├── FraudDetectionInterface.php
│ │ └── SiftScienceAdapter.php
│ │
│ ├── Cache/ # Cache specific to context
│ │ ├── UserCacheRepository.php
│ │ └── SessionCacheRepository.php
│ │
│ ├── Routes/ # Context routes
│ │ ├── api.php # API Routes
│ │ ├── web.php # Web/Inertia Routes
│ │ ├── channels.php # 🔥 Broadcasting Channels (Reverb)
│ │ └── console.php # Console Routes
│ │
│ └── Views/ # Views (if using Blade instead of Inertia)
│ ├── emails/
│ │ ├── otp.blade.php
│ │ └── welcome.blade.php
│ └── auth/
│ └── login.blade.php
│
│
├── Users/ # 👤 CONTEXT: User Management (full CRUD — base template)
│ │
│ ├── Providers/
│ │ └── UsersServiceProvider.php
│ │
│ ├── Config/
│ │ └── users.php
│ │
│ ├── Contracts/ # 🔥 Published Language (context public API)
│ │ └── DTOs/
│ │ └── UserSummaryDTO.php # DTO exposed to other contexts (does not expose internal entity)
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ ├── Domain/
│ │ │ └── Application/
│ │ ├── Integration/
│ │ │ └── Infrastructure/
│ │ └── Feature/
│ │ ├── AdminCrudUserTest.php # 🔥 Super Admin creates, edits, suspends, deletes users
│ │ ├── SetFirstTimePasswordTest.php # 🔥 Full welcome flow → link → set password
│ │ └── AdminResetPasswordTest.php # 🔥 Admin resets password → email → user sets new one
│ │
│ ├── Domain/
│ │ ├── Entities/
│ │ │ ├── User.php # 🧬 #[Override] $id: UserId — root entity for this context
│ │ │ ├── UserProfile.php
│ │ │ ├── UserPreferences.php
│ │ │ └── UserActivity.php
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── UserId.php # 🧬 readonly + clone with
│ │ │ ├── FullName.php # 🧬 readonly + clone with
│ │ │ ├── Avatar.php # 🧬 readonly + clone with
│ │ │ ├── Bio.php # 🧬 readonly + clone with
│ │ │ └── SocialLinks.php # 🧬 readonly + clone with
│ │ │
│ │ ├── Enums/ # 🔥 PHP 8.1+ Enums
│ │ │ ├── UserStatus.php # active, suspended, banned, deleted
│ │ │ └── ProfileVisibility.php # public, private, friends_only
│ │ │
│ │ ├── Ports/
│ │ │ ├── UserRepositoryPort.php # 🔥 Main port — full CRUD
│ │ │ ├── UserProfileRepositoryPort.php
│ │ │ └── UserActivityRepositoryPort.php
│ │ │
│ │ ├── Services/
│ │ │ ├── UserProfileManager.php # 🧬 #[NoDiscard]
│ │ │ └── UserStatusManager.php # 🔥 Business logic: suspend, ban, activate
│ │ │
│ │ ├── Policies/ # 🔥 Domain Policies
│ │ │ └── CanManageUsersPolicy.php # Only SuperAdmin can create/delete users
│ │ │
│ │ ├── Events/
│ │ │ ├── UserCreatedByAdmin.php # 🔥 Admin created user → triggers welcome email
│ │ │ ├── ProfileUpdated.php
│ │ │ ├── AvatarChanged.php
│ │ │ ├── UserSuspended.php
│ │ │ ├── UserActivated.php
│ │ │ └── UserDeleted.php
│ │ │
│ │ ├── Subscribers/ # 🔥 Synchronous reactions
│ │ │ └── InvalidateUserCacheOnUpdate.php
│ │ │
│ │ ├── Specifications/ # 🧬 #[NoDiscard] PHP 8.5
│ │ │ └── UserEmailMustBeUnique.php # 🧬 #[NoDiscard] on isSatisfiedBy()
│ │ │
│ │ └── Exceptions/
│ │ ├── UserNotFoundException.php
│ │ ├── UserAlreadyExistsException.php
│ │ ├── ProfileNotFoundException.php
│ │ └── InvalidAvatarException.php
│ │
│ ├── Application/
│ │ ├── Commands/
│ │ │ │
│ │ │ ├── CreateUser/ # 🔥 Super Admin creates user → sends welcome email with setup link
│ │ │ │ ├── CreateUserCommand.php # name, email, role — no password (user defines it)
│ │ │ │ └── CreateUserHandler.php # Creates user with status=pending_setup → triggers WelcomeSetupEmail
│ │ │ │
│ │ │ ├── UpdateUser/ # 🔥 Super Admin edits user data
│ │ │ │ ├── UpdateUserCommand.php # name, email, role, status
│ │ │ │ └── UpdateUserHandler.php
│ │ │ │
│ │ │ ├── AdminResetPassword/ # 🔥 Super Admin forces password reset → sends email to user
│ │ │ │ ├── AdminResetPasswordCommand.php # userId — generates SetupToken and sends email
│ │ │ │ └── AdminResetPasswordHandler.php # Invalidates current password → new token → email
│ │ │ │
│ │ │ ├── SuspendUser/ # 🔥 Super Admin suspends user
│ │ │ │ ├── SuspendUserCommand.php
│ │ │ │ └── SuspendUserHandler.php
│ │ │ │
│ │ │ ├── ActivateUser/ # 🔥 Super Admin reactivates suspended user
│ │ │ │ ├── ActivateUserCommand.php
│ │ │ │ └── ActivateUserHandler.php
│ │ │ │
│ │ │ ├── DeleteUser/ # 🔥 Soft delete
│ │ │ │ ├── DeleteUserCommand.php
│ │ │ │ └── DeleteUserHandler.php
│ │ │ │
│ │ │ ├── UpdateProfile/
│ │ │ │ ├── UpdateProfileCommand.php
│ │ │ │ └── UpdateProfileHandler.php
│ │ │ │
│ │ │ └── UploadAvatar/
│ │ │ ├── UploadAvatarCommand.php
│ │ │ └── UploadAvatarHandler.php
│ │ │
│ │ ├── Queries/
│ │ │ ├── GetUser/
│ │ │ │ ├── GetUserQuery.php
│ │ │ │ └── GetUserHandler.php
│ │ │ │
│ │ │ ├── ListUsers/ # 🔥 Paginated list with filters for Super Admin
│ │ │ │ ├── ListUsersQuery.php # filters: status, role, search, dateRange
│ │ │ │ └── ListUsersHandler.php
│ │ │ │
│ │ │ ├── GetUserProfile/
│ │ │ │ ├── GetUserProfileQuery.php
│ │ │ │ └── GetUserProfileHandler.php
│ │ │ │
│ │ │ ├── GetUserActivity/
│ │ │ │ ├── GetUserActivityQuery.php
│ │ │ │ └── GetUserActivityHandler.php
│ │ │ │
│ │ │ ├── SearchUsers/
│ │ │ │ ├── SearchUsersQuery.php
│ │ │ │ └── SearchUsersHandler.php
│ │ │ │
│ │ │ └── ReadModels/ # 🔥 CQRS Read Models
│ │ │ ├── UserReadModel.php
│ │ │ ├── UserListReadModel.php # 🔥 Optimized view for admin table
│ │ │ └── UserProfileReadModel.php
│ │ │
│ │ ├── DTOs/
│ │ │ ├── CreateUserDTO.php
│ │ │ ├── UpdateUserDTO.php
│ │ │ └── UserFilterDTO.php
│ │ │
│ │ ├── IntegrationEvents/ # 🔥 Events published by this context
│ │ │ ├── UserCreatedIntegration.php # 🔥 Notifies Auth context to create credentials
│ │ │ ├── ProfileUpdatedIntegration.php
│ │ │ └── UserDeletedIntegration.php
│ │ │
│ │ └── EventHandlers/
│ │ ├── OnUserCreatedByAdmin.php # 🔥 Listens to UserCreatedByAdmin → dispatches WelcomeSetupEmailJob
│ │ ├── OnProfileUpdated.php
│ │ └── OnUserDeleted.php
│ │
│ └── Infrastructure/
│ ├── Adapters/
│ │ ├── Http/
│ │ │ ├── Controllers/
│ │ │ │ ├── Api/
│ │ │ │ │ ├── AdminUserController.php # 🔥 Full CRUD — SuperAdmin only
│ │ │ │ │ │ # GET /api/admin/users (index)
│ │ │ │ │ │ # POST /api/admin/users (store — creates user + sends email)
│ │ │ │ │ │ # GET /api/admin/users/{id} (show)
│ │ │ │ │ │ # PUT /api/admin/users/{id} (update)
│ │ │ │ │ │ # DELETE /api/admin/users/{id} (destroy)
│ │ │ │ │ │ # POST /api/admin/users/{id}/suspend
│ │ │ │ │ │ # POST /api/admin/users/{id}/activate
│ │ │ │ │ │ # POST /api/admin/users/{id}/reset-password (sends reset email)
│ │ │ │ │ └── UserProfileController.php # Authenticated user's own profile
│ │ │ │ │
│ │ │ │ └── Web/
│ │ │ │ └── ProfilePageController.php
│ │ │ │
│ │ │ ├── Requests/
│ │ │ │ ├── CreateUserRequest.php # 🔥 name, email, role — no password (user sets it)
│ │ │ │ ├── UpdateUserRequest.php # 🔥 name, email, role, status
│ │ │ │ ├── UpdateProfileRequest.php
│ │ │ │ └── UploadAvatarRequest.php
│ │ │ │
│ │ │ └── Resources/
│ │ │ ├── UserResource.php # 🔥 Includes setup_status for admin
│ │ │ ├── UserListResource.php # 🔥 Compact version for admin table
│ │ │ ├── UserProfileResource.php
│ │ │ ├── UserCollection.php
│ │ │ └── UserActivityResource.php
│ │ │
│ │ ├── WebSocket/ # 🔥 WebSocket for Users (Reverb)
│ │ │ ├── Channels/
│ │ │ │ └── UserPresenceChannel.php
│ │ │ │
│ │ │ ├── Events/
│ │ │ │ ├── ProfileUpdatedBroadcast.php
│ │ │ │ └── UserStatusChangedBroadcast.php
│ │ │ │
│ │ │ └── Listeners/
│ │ │ └── BroadcastProfileUpdate.php
│ │ │
│ │ └── CLI/
│ │ └── Commands/
│ │ ├── SyncUserDataCommand.php
│ │ └── ResendSetupEmailCommand.php # 🔥 Resends welcome email to pending_setup users
│ │
│ ├── Persistence/
│ │ ├── Eloquent/
│ │ │ ├── Models/
│ │ │ │ ├── UserEloquentModel.php # @internal — Infrastructure only. Use UserRepositoryPort.
│ │ │ │ │ # HasRoles (spatie), HasApiTokens (sanctum), SoftDeletes
│ │ │ │ │ # setup_status: pending_setup | active
│ │ │ │ ├── UserProfileEloquentModel.php # @internal — Infrastructure only.
│ │ │ │ └── UserActivityEloquentModel.php # @internal — Infrastructure only.
│ │ │ │
│ │ │ ├── Migrations/
│ │ │ │ ├── 2026_01_create_users_table.php # 🔥 +setup_status, +setup_token_expires_at
│ │ │ │ ├── 2026_02_create_user_profiles_table.php
│ │ │ │ └── 2026_03_create_user_activities_table.php
│ │ │ │
│ │ │ ├── Seeders/
│ │ │ │ ├── SuperAdminSeeder.php # 🔥 Creates the first Super Admin
│ │ │ │ └── UsersSeeder.php
│ │ │ │
│ │ │ └── Factories/
│ │ │ └── UserFactory.php # For PEST tests
│ │ │
│ │ ├── Repositories/
│ │ │ ├── EloquentUserRepository.php # Implements UserRepositoryPort
│ │ │ ├── EloquentUserProfileRepository.php
│ │ │ └── EloquentUserActivityRepository.php
│ │ │
│ │ └── Mappers/
│ │ ├── UserMapper.php
│ │ ├── UserProfileMapper.php
│ │ └── UserActivityMapper.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ ├── ProcessAvatarJob.php # 🔥 Uses intervention/image
│ │ └── GenerateThumbnailsJob.php # 🔥 Uses intervention/image
│ │
│ ├── ExternalServices/
│ │ └── Storage/
│ │ ├── StorageInterface.php
│ │ ├── S3StorageAdapter.php # 🔥 league/flysystem-aws-s3-v3
│ │ ├── LocalStorageAdapter.php
│ │ ├── CloudinaryAdapter.php
│ │ └── SpatieMediaLibraryAdapter.php # 🔥 spatie/laravel-medialibrary
│ │
│ ├── Cache/
│ │ └── UserCacheRepository.php
│ │
│ └── Routes/
│ ├── api.php # 🔥 Includes /admin/users/\* routes protected by role:super-admin
│ ├── web.php
│ └── channels.php # 🔥 Reverb channels
│
│
├── Notifications/ # 📧 CONTEXT: Notifications
│ │
│ ├── Providers/
│ │ └── NotificationsServiceProvider.php
│ │
│ ├── Config/
│ │ └── notifications.php
│ │
│ ├── Contracts/ # 🔥 Published Language (context public API)
│ │ └── DTOs/
│ │ └── NotificationSummaryDTO.php # DTO exposed to other contexts
│ │
│ ├── Tests/
│ │ ├── Unit/
│ │ │ ├── Domain/
│ │ │ └── Application/
│ │ ├── Integration/
│ │ │ └── Infrastructure/
│ │ └── Feature/
│ │ ├── SendNotificationTest.php
│ │ └── RealtimeNotificationTest.php # 🔥 WebSocket delivery tests
│ │
│ ├── Domain/
│ │ ├── Entities/
│ │ │ ├── Notification.php
│ │ │ └── NotificationPreference.php
│ │ │
│ │ ├── ValueObjects/
│ │ │ ├── NotificationType.php
│ │ │ └── NotificationChannel.php
│ │ │
│ │ ├── Enums/ # 🔥 PHP 8.1+ Enums
│ │ │ ├── NotificationStatus.php # pending, sent, read, failed
│ │ │ └── DeliveryChannel.php # email, sms, push, in_app, websocket
│ │ │
│ │ └── Ports/
│ │ └── NotificationRepositoryPort.php
│ │
│ ├── Application/
│ │ ├── Commands/
│ │ │ └── SendNotification/
│ │ │
│ │ └── Queries/
│ │ └── GetUserNotifications/
│ │
│ └── Infrastructure/
│ ├── Adapters/
│ │ ├── Http/
│ │ │ └── Controllers/
│ │ │ └── NotificationController.php
│ │ │
│ │ ├── WebSocket/ # 🔥 Real-time notifications (Reverb)
│ │ │ │
│ │ │ ├── Channels/
│ │ │ │ ├── NotificationChannel.php
│ │ │ │ └── UserNotificationChannel.php
│ │ │ │
│ │ │ ├── Events/
│ │ │ │ ├── NotificationSentBroadcast.php
│ │ │ │ └── NotificationReadBroadcast.php
│ │ │ │
│ │ │ └── Listeners/
│ │ │ └── BroadcastNotificationSent.php
│ │ │
│ │ └── CLI/
│ │ └── Commands/
│ │ └── SendBulkNotificationsCommand.php
│ │
│ ├── Persistence/
│ │ ├── Eloquent/
│ │ │ ├── Models/
│ │ │ │ ├── NotificationEloquentModel.php # @internal — Infrastructure only. Use NotificationRepositoryPort.
│ │ │ │ └── NotificationPreferenceEloquentModel.php # @internal — Infrastructure only.
│ │ │ ├── Migrations/
│ │ │ │ ├── 2026_01_create_notifications_table.php
│ │ │ │ └── 2026_02_create_notification_preferences_table.php
│ │ │ └── Factories/
│ │ │ └── NotificationFactory.php
│ │ │
│ │ ├── Repositories/
│ │ │ └── EloquentNotificationRepository.php # Implements NotificationRepositoryPort
│ │ │
│ │ └── Mappers/ # 🔥 Eloquent ↔ Domain Mappers
│ │ └── NotificationMapper.php
│ │
│ ├── Queue/
│ │ └── Jobs/
│ │ └── SendNotificationJob.php
│ │
│ ├── ExternalServices/
│ │ ├── Push/
│ │ │ ├── FirebaseAdapter.php
│ │ │ └── OneSignalAdapter.php
│ │ │
│ │ └── Realtime/ # 🔥 Reverb as external service
│ │ ├── RealtimeNotificationInterface.php
│ │ └── ReverbNotificationAdapter.php
│ │
│ └── Routes/
│ ├── api.php
│ └── channels.php # 🔥 Reverb notification channels
│
│
└── AI/ # 🤖 CONTEXT: Artificial Intelligence
│
├── Providers/
│ └── AIServiceProvider.php
│
├── Config/
│ └── ai.php
│
├── Contracts/ # 🔥 Published Language (context public API)
│ └── DTOs/
│ ├── GenerationResultDTO.php # DTO exposed to other contexts
│ └── AIModelInfoDTO.php
│
├── Tests/
│ ├── Unit/
│ │ ├── Domain/
│ │ └── Application/
│ ├── Integration/
│ │ └── Infrastructure/
│ └── Feature/
│ ├── GenerateContentTest.php
│ ├── AnalyzeTextTest.php
│ └── AIStreamingTest.php # 🔥 WebSocket streaming tests
│
├── Domain/
│ ├── Ports/
│ │ └── LLMProviderPort.php # Abstract interface for any LLM
│ │
│ ├── ValueObjects/
│ │ ├── Prompt.php
│ │ ├── AIResponse.php
│ │ └── StructuredSchema.php
│ │
│ ├── Enums/ # 🔥 PHP 8.1+ Enums
│ │ ├── AIProvider.php # openai, anthropic, gemini, ollama
│ │ └── ResponseFormat.php # text, structured, stream
│ │
│ └── Services/
│ ├── ContentGenerator.php
│ └── TextAnalyzer.php
│
├── Application/
│ ├── Commands/
│ │ ├── GenerateContent/
│ │ │ ├── GenerateContentCommand.php
│ │ │ └── GenerateContentHandler.php
│ │ │
│ │ └── AnalyzeText/
│ │ ├── AnalyzeTextCommand.php
│ │ └── AnalyzeTextHandler.php
│ │
│ └── Queries/
│ └── GetAIModels/
│ ├── GetAIModelsQuery.php
│ └── GetAIModelsHandler.php
│
└── Infrastructure/
├── Adapters/
│ ├── Http/
│ │ └── Controllers/
│ │ └── AIController.php
│ │
│ └── WebSocket/ # 🔥 Real-time AI streaming (Reverb)
│ └── Events/
│ └── AIResponseStreamBroadcast.php
│
├── Persistence/
│ ├── Eloquent/
│ │ ├── Models/
│ │ │ └── AIRequestLogEloquentModel.php # @internal — Infrastructure only. Audit log of AI calls.
│ │ ├── Migrations/
│ │ │ └── 2026_01_create_ai_request_logs_table.php
│ │ └── Factories/
│ │ └── AIRequestLogFactory.php
│ │
│ └── Repositories/
│ └── EloquentAIRequestLogRepository.php
│
├── Queue/
│ └── Jobs/
│ ├── GenerateContentAsyncJob.php # 🔥 For long-running AI generation
│ └── AnalyzeTextAsyncJob.php
│
├── ExternalServices/
│ ├── Prism/ # 🔥 prism-php/prism
│ │ ├── PrismLLMAdapter.php # Text, chat, conversation
│ │ └── PrismStructuredAdapter.php # Structured output with DTOs
│ │
│ └── FastAPI/ # 🔥 Python microservice (heavy ML)
│ ├── FastAPIClientInterface.php # 🔥 Contract for HTTP communication
│ ├── FastAPIAdapter.php # 🔥 Implementation with Guzzle/Http
│ ├── FastAPIHealthCheck.php # 🔥 Health check for the Python service
│ ├── DTOs/
│ │ ├── PredictionRequestDTO.php # 🔥 Request to ML model
│ │ ├── PredictionResponseDTO.php # 🔥 Response from ML model
│ │ └── ModelInfoDTO.php # 🔥 ML model metadata
│ ├── Endpoints/ # 🔥 FastAPI route definitions
│ │ ├── PredictionEndpoint.php # POST /predict
│ │ ├── EmbeddingEndpoint.php # POST /embed
│ │ ├── ClassifyEndpoint.php # POST /classify
│ │ └── TrainEndpoint.php # POST /train (fine-tuning)
│ └── Config/
│ └── fastapi.php # 🔥 base_url, timeout, retry, api_key
│
└── Routes/
├── api.php
└── channels.php # 🔥 AI streaming channels

    │
    └── {YourContext}/                               # ⭐ TEMPLATE — Replace with your real context
        │   # ──────────────────────────────────────────────────────────────────────────────────────
        │   # HOW TO USE THIS TEMPLATE:
        │   # 1. Duplicate this entire block for each new bounded context
        │   # 2. Replace {YourContext} with the context name (e.g.: Orders, Products, Inventory)
        │   # 3. Replace {YourEntity} with the main entity (e.g.: Order, Product, Item)
        │   # 4. Replace {YourId} with the Value Object ID (e.g.: OrderId, ProductId)
        │   # 5. Auth and Users contexts are the full reference — this template is the minimum
        │   # 6. Register routes in Infrastructure/Routes/web.php
        │   # 7. If the module requires user_id by relationship, add it as well as the belongsTo or hasMany from User to that table
        │   # ──────────────────────────────────────────────────────────────────────────────────────
        │
        ├── Providers/
        │   └── {YourContext}ServiceProvider.php     # ⚠️ REQUIRED: bind Ports → Repositories in register()
        │
        ├── Config/
        │   └── {your_context}.php
        │
        ├── Contracts/                               # 🔥 Published Language — public API toward other contexts
        │   ├── {YourContext}ServiceContract.php
        │   └── DTOs/
        │       └── {YourEntity}OutputDTO.php
        │
        ├── Tests/
        │   ├── Unit/
        │   │   ├── Domain/
        │   │   └── Application/
        │   ├── Integration/
        │   │   └── Infrastructure/
        │   └── Feature/
        │       ├── {YourEntity}CrudTest.php         # PEST: happy paths + validation + soft delete
        │       └── {YourEntity}ExportTest.php       # PEST: Excel + PDF export endpoints
        │
        ├── Domain/                                  # ⭐ LAYER 1: Pure Domain
        │   │
        │   ├── Entities/
        │   │   └── {YourEntity}.php                 # 🧬 extends AggregateRoot, #[Override] $id: {YourId}
        │   │
        │   ├── ValueObjects/
        │   │   ├── {YourId}.php                     # 🧬 readonly + clone with (ramsey/uuid)
        │   │   └── ...                              # Other domain Value Objects
        │   │
        │   ├── Enums/                               # 🧬 PHP 8.1+ Backed Enums
        │   │   └── {YourEntity}Status.php           # e.g.: active, inactive, archived, deleted
        │   │
        │   ├── Ports/                               # Repository interfaces
        │   │   └── {YourEntity}RepositoryPort.php
        │   │
        │   ├── Services/                            # 🧬 #[NoDiscard] — results must never be ignored
        │   │   └── {YourEntity}DomainService.php
        │   │
        │   ├── Policies/                            # Domain authorization
        │   │   └── Can{Action}{YourEntity}Policy.php
        │   │
        │   ├── Events/                              # Domain Events
        │   │   ├── {YourEntity}Created.php
        │   │   ├── {YourEntity}Updated.php
        │   │   └── {YourEntity}Deleted.php
        │   │
        │   ├── Subscribers/                         # Synchronous reactions (same transaction)
        │   │   └── On{YourEntity}Deleted.php
        │   │
        │   ├── Specifications/                      # 🧬 #[NoDiscard] on isSatisfiedBy()
        │   │   └── {YourEntity}MustBeUnique.php
        │   │
        │   └── Exceptions/
        │       ├── {YourEntity}NotFoundException.php
        │       └── {YourEntity}AlreadyExistsException.php
        │
        ├── Application/                             # ⚙️ LAYER 2: Use Cases (CQRS)
        │   │
        │   ├── Commands/                            # Write operations
        │   │   ├── Create{YourEntity}/
        │   │   │   ├── Create{YourEntity}Command.php
        │   │   │   └── Create{YourEntity}Handler.php
        │   │   ├── Update{YourEntity}/
        │   │   │   ├── Update{YourEntity}Command.php
        │   │   │   └── Update{YourEntity}Handler.php
        │   │   └── Delete{YourEntity}/              # Soft delete — sets deleted_at
        │   │       ├── Delete{YourEntity}Command.php
        │   │       └── Delete{YourEntity}Handler.php
        │   │
        │   ├── Queries/                             # Read operations
        │   │   ├── Get{YourEntity}List/
        │   │   │   ├── Get{YourEntity}ListQuery.php # Accepts FilterDTO (page, perPage, search, dateFrom, dateTo, sortBy, sortDir)
        │   │   │   ├── Get{YourEntity}ListHandler.php
        │   │   │   └── ReadModels/
        │   │   │       └── {YourEntity}ListReadModel.php
        │   │   └── Get{YourEntity}/
        │   │       ├── Get{YourEntity}Query.php
        │   │       ├── Get{YourEntity}Handler.php
        │   │       └── ReadModels/
        │   │           └── {YourEntity}DetailReadModel.php
        │   │
        │   ├── DTOs/
        │   │   ├── {YourEntity}FilterDTO.php        # spatie/laravel-data — dateFrom, dateTo, search, status, sortBy, sortDir
        │   │   ├── Create{YourEntity}DTO.php        # spatie/laravel-data
        │   │   └── Update{YourEntity}DTO.php        # spatie/laravel-data
        │   │
        │   ├── IntegrationEvents/
        │   │   └── {YourEntity}CreatedIntegration.php
        │   │
        │   └── EventHandlers/
        │       └── On{YourEntity}Created.php
        │
        └── Infrastructure/                          # 🔌 LAYER 3: Adapters
            │
            ├── Adapters/
            │   ├── Http/
            │   │   ├── Controllers/
            │   │   │   ├── Api/
            │   │   │   │   ├── {YourEntity}Controller.php        # index, show, store, update, destroy
            │   │   │   │   └── {YourEntity}ExportController.php  # 🔥 GET /export?format=excel|pdf + FilterDTO
            │   │   │   └── Web/
            │   │   │       └── {YourEntity}PageController.php    # Inertia responses
            │   │   │
            │   │   ├── Requests/
            │   │   │   ├── {YourEntity}FilterRequest.php  # Validates: page, perPage, search, dateFrom≤dateTo, status
            │   │   │   ├── Create{YourEntity}Request.php
            │   │   │   └── Update{YourEntity}Request.php
            │   │   │
            │   │   ├── Resources/
            │   │   │   ├── {YourEntity}Resource.php
            │   │   │   └── {YourEntity}Collection.php
            │   │   │
            │   │   └── Export/                          # 🔥 Per-context export classes
            │   │       ├── {YourEntity}ExcelExport.php  # implements FromQuery + WithHeadings + WithMapping + ShouldAutoSize
            │   │       └── {YourEntity}PdfExport.php    # Uses Core PdfAdapter — cursor() + Blade template
            │   │
            │   ├── WebSocket/                           # 🔥 Real-time updates via Reverb (if needed)
            │   │   ├── Channels/
            │   │   │   └── {YourEntity}Channel.php
            │   │   └── Events/
            │   │       └── {YourEntity}UpdatedBroadcast.php
            │   │
            │   └── CLI/
            │       └── Commands/
            │           └── Sync{YourEntity}DataCommand.php
            │
            ├── Persistence/
            │   ├── Eloquent/
            │   │   ├── Models/
            │   │   │   └── {YourEntity}EloquentModel.php  # @internal — Infrastructure only. Use {YourEntity}RepositoryPort.
            │   │   │                                       #   SoftDeletes, timestamps, scopeInDateRange()
            │   │   ├── Migrations/
            │   │   │   └── 2026_xx_create_{your_entities}_table.php  # id, ...fields, created_at, updated_at, deleted_at
            │   │   ├── Seeders/
            │   │   │   └── {YourEntity}Seeder.php
            │   │   └── Factories/
            │   │       └── {YourEntity}Factory.php         # For PEST tests
            │   │
            │   ├── Repositories/
            │   │   └── Eloquent{YourEntity}Repository.php  # Implements {YourEntity}RepositoryPort
            │   │
            │   └── Mappers/
            │       └── {YourEntity}Mapper.php              # Eloquent ↔ Domain Entity
            │
            ├── Queue/
            │   └── Jobs/
            │       └── Export{YourEntity}QueuedJob.php     # For large exports (>50MB) — ShouldQueue
            │
            ├── Resources/
            │   └── views/
            │       └── pdf/
            │           └── {your_context}/
            │               └── {your_entity}-export.blade.php  # PDF Template: inline CSS, base64 logo, filter summary
            │
            └── Routes/
                ├── api.php      # GET /api/{your-context}/{your-entities} (index, show, store, update, destroy)
                │               # GET /api/{your-context}/{your-entities}/export?format=excel|pdf
                ├── web.php      # Inertia routes
                └── channels.php # Reverb channels

---

🔥 Key Implemented Changes:

Repositories → Ports in Domain
Infrastructure/Adapters/ groups HTTP, CLI, WebSocket
Infrastructure/Queue/ separated with Jobs + Handlers
WebSocket/ with Reverb in each context that needs it
Core/Shared/Infrastructure/Broadcasting/ for global Reverb configuration
CLI/Commands/Maintenance/ for optimization commands
Health checks for Reverb in Observability

🔥 Enterprise 2026 Corrections (Architectural Audit):

1.  Anti-Corruption Layer (ACL) between Bounded Contexts — avoids direct coupling
2.  IntegrationEvents/ in each context — async communication between contexts
3.  Contracts/ (Published Language) — public API each context exposes
4.  Domain Policies/ — authorization rules in the domain (not just middleware)
5.  Domain Subscribers/ — synchronous reactions within the same transaction
6.  Enums/ with PHP 8.1+ — replaces magic strings with type-safe values
7.  ReadModels moved to Application/Queries/ — pure CQRS
8.  Additional exceptions: ConcurrencyException, InvariantViolationException, IntegrationException
9.  Audit/ cross-cutting for spatie/laravel-activitylog
10. Export/ cross-cutting for maatwebsite/excel + barryvdh/laravel-dompdf — ExcelAdapter + PdfAdapter + PdfTemplateRenderer in Core/Shared
11. SpatieMediaLibraryAdapter for advanced media management
12. ResendAdapter + ReactEmailTemplateRenderer for transactional emails
13. OpenTelemetry SDK 1.x as the 2026 standard (CNCF Graduated) — OTLP/gRPC as single protocol; Jaeger/Zipkin receive via OTLP without custom exporters
14. HealthCheckController + HealthCheckAggregator + ExternalServiceHealthCheck — complete /health endpoint
15. UnitOfWork + TransactionalCommandBus + RollbackStrategy — enterprise DB transactions with rollback
16. Complete Authorization Layer: Gates/, Guards/ (Sanctum, Session, OTP), LaravelPolicies/ + AuthorizationService
17. Refactored Logging: Monolog as central bus with Handlers/ (OpenTelemetryMonologHandler, StructuredJsonHandler) + Processors/ (TraceContextProcessor, CorrelationIdProcessor, RequestContextProcessor) — trace_id/span_id automatically injected in all logs
18. InstrumentationProvider for OTEL auto-instrumentation: SQL queries, HTTP client, Redis — no manual code
19. CircuitBreakerMetricsExporter → Prometheus: open/closed/half-open state visible in dashboards
20. @internal on all Eloquent Models — enforced via PHPStan level 8 (see phpstan.neon below)
21. Architecture tests via PHPArkitect — layer boundaries enforced in CI (see tests/Architecture/)

📐 Context Reference — Usage Guide:

Auth/ → Complete authentication: OTP, sessions, permissions, guards, tokens, OAuth + SetupPassword flow: first access via link (registration) and admin reset + SetupToken ValueObject + UserSetupStatus enum (pending_setup | active) + SetupPasswordController: GET+POST /auth/setup-password/{token}
Users/ → Complete enterprise CRUD — base template for any new module:
Domain (User Entity + ValueObjects + Enums + Ports + Events + Specifications)
Application (Commands CRUD: Create, Update, Delete, Suspend, Activate, AdminResetPassword)
Application (Queries: ListUsers paginated with filters, GetUser, SearchUsers, ReadModels)
Infrastructure (AdminUserController with 8 routes, Requests, Resources, Persistence, Routes)
NOTE: Super Admin manages users fully. Creation sends a welcome email
with first-access link. AdminResetPassword sends email so user sets their new password.
{YourContext}/ → Generic template at the end of the tree — duplicate and replace names for each new module

🔐 CRUD Module — Role & Permission Naming Convention:

When creating a new CRUD module, ALWAYS follow these naming rules for roles and permissions:

Role Naming:

- Single-word module → `Module` (e.g. `Users`, `Claims`, `Billing`)
- Two-word module → `Module_Module` (e.g. `Water_Quality`, `Work_Orders`, `Service_Areas`)
- Always UPPERCASE first letter per word, joined by underscore

Permission Naming — Four base actions per module:

- `VIEW_{MODULE}` → e.g. `VIEW_USERS`, `VIEW_WATER_QUALITY`
- `CREATE_{MODULE}` → e.g. `CREATE_USERS`, `CREATE_WATER_QUALITY`
- `UPDATE_{MODULE}` → e.g. `UPDATE_USERS`, `UPDATE_WATER_QUALITY`
- `DELETE_{MODULE}` → e.g. `DELETE_USERS`, `DELETE_WATER_QUALITY`

Additional permissions may be added per module (e.g. `EXPORT_USERS`, `SUSPEND_USERS`).

Seeder Rule — MANDATORY in every PermissionsSeeder:

```php
// ALWAYS call this AFTER batch-creating permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

🧬 PHP 8.5 Features Applied:

1.  readonly + clone with → ALL Value Objects (Uuid, Email, Money, OtpCode, etc.)
2.  #[NoDiscard] → Domain Services and Specifications (result must never be ignored)
3.  #[Override] on Properties → Entities that extend AggregateRoot ($id typed)
4.  Pipe Operator |> → Pipes (Validation, Transformation, Sanitization), Mappers, Handlers
5.  Closures in Constant Expressions → DTOs with spatie/laravel-data (validation rules)
6.  PHP 8.5 Uri Extension → ValueObjects/Url.php (replaces manual parse_url)
7.  array_first() / array_last() → Replaces reset()/end() throughout the codebase

📦 Composer Packages → Architecture Mapping:

open-telemetry/opentelemetry-php → Core/Shared/Infrastructure/Observability/Tracing/
open-telemetry/exporter-otlp → Core/Shared/Infrastructure/Observability/Tracing/OtlpGrpcExporter.php
monolog/monolog → Core/Shared/Infrastructure/Logging/ (central bus + Processors/Handlers)
intervention/image → Contexts/Users/Infrastructure/Queue/Jobs/ (image processing)
laravel/socialite → Contexts/Auth/Infrastructure/ExternalServices/OAuth/
league/flysystem-aws-s3-v3 → Contexts/Users/Infrastructure/ExternalServices/Storage/
maatwebsite/excel → Core/Shared/Infrastructure/Export/ExcelAdapter.php (+ per-context {Module}ExcelExport.php)
barryvdh/laravel-dompdf → Core/Shared/Infrastructure/Export/PdfAdapter.php (+ per-context {Module}PdfExport.php + resources/views/pdf/{context}/)
ramsey/uuid → Core/Shared/Domain/ValueObjects/Uuid.php
spatie/laravel-cookie-consent → Core/Middleware/CookieConsentMiddleware.php
spatie/laravel-permission → Contexts/Auth/Application/Services/PermissionService.php
spatie/laravel-data → Core/Shared/Application/DTOs/BaseDTO.php (extends Data)
laravel/fortify → Contexts/Auth/Application/Services/AuthenticationService.php
spatie/laravel-one-time-passwords → Contexts/Auth/Domain/Services/OtpGenerator.php
resend/resend-laravel → Contexts/Auth/Infrastructure/ExternalServices/Email/ResendAdapter.php
laravel/reverb → Core/Shared/Infrastructure/Broadcasting/ReverbAdapter.php
laravel/telescope → Core/Shared/Infrastructure/Observability/
laravel/horizon → Core/Shared/Infrastructure/Queue/
spatie/laravel-activitylog → Core/Shared/Infrastructure/Audit/SpatieActivityLogAdapter.php
spatie/laravel-backup → Core/Shared/Infrastructure/
spatie/laravel-medialibrary → Contexts/Users/Infrastructure/ExternalServices/Storage/
laravel/sanctum → Contexts/Auth/ (HasApiTokens on UserEloquentModel)
pestphp/pest → Contexts/\*/Tests/ (Unit, Integration, Feature)
phparkitect/phparkitect → tests/Architecture/ (layer boundary enforcement in CI) 🔥 NEW
barryvdh/laravel-debugbar → Dev only (APP_DEBUG=true)
barryvdh/laravel-ide-helper → Dev only (\_ide_helper.php)

---

🛡️ Architecture Enforcement (NEW — Mapper Strategy Completion):

The Mapper pattern (Eloquent ↔ Domain) is defined in the folder structure above.
These two additions make it self-enforcing — no discipline required from the team.

① @internal on ALL Eloquent Models
───────────────────────────────────
Every \*EloquentModel.php must carry a @internal docblock.
This signals to PHPStan that usage outside the Infrastructure namespace is forbidden.

Example — UserEloquentModel.php:

    /**
     * Eloquent persistence model for the Users context.
     *
     * @internal Only for use within Infrastructure\Persistence.
     *           Outside this layer, always inject UserRepositoryPort.
     */
    final class UserEloquentModel extends Model
    {
        use HasRoles, HasApiTokens, SoftDeletes;
        // ...
    }

Apply the same @internal docblock to:

- OtpEloquentModel.php
- SessionEloquentModel.php
- RefreshTokenEloquentModel.php
- UserProfileEloquentModel.php
- UserActivityEloquentModel.php
- {YourEntity}EloquentModel.php ← every new model going forward

② PHPStan Configuration (phpstan.neon)
────────────────────────────────────────
Place this file at the project root. Level 8 activates @internal enforcement.

    # phpstan.neon
    includes:
        - vendor/nunomaduro/larastan/extension.neon

    parameters:
        level: 8
        paths:
            - src
            - tests
        checkInternalClassCaseSensitivity: true
        internalTag: '@internal'

        # Ignore IDE helper generated files
        excludePaths:
            - _ide_helper.php
            - _ide_helper_models.php

        # Treat missing typehints as errors
        reportUnmatchedIgnoredErrors: true
        checkMissingIterableValueType: true
        checkGenericClassInNonGenericObjectType: true

With this config, any class outside Infrastructure\Persistence that imports
a model annotated with @internal will cause a CI failure before merge.

③ Architecture Tests (tests/Architecture/)
────────────────────────────────────────────
Install: composer require phparkitect/phparkitect --dev

tests/
└── Architecture/
├── LayerBoundariesTest.php # Domain/Application must not touch Infrastructure/Eloquent
├── NamingConventionsTest.php # Handlers end in Handler, Commands in Command, etc.
└── DependencyRulesTest.php # Application layer cannot import Infrastructure directly

── LayerBoundariesTest.php ──────────────────────────────────────────────────

    <?php

    declare(strict_types=1);

    use Arkitect\Architecture;
    use Arkitect\ClassSet;
    use Arkitect\CLI\Check;
    use Arkitect\Expression\ForClasses\NotDependOnTheseNamespaces;
    use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
    use Arkitect\Rules\Rule;

    it('domain layer does not depend on infrastructure', function (): void {
        $classSet = ClassSet::fromDir(__DIR__ . '/../../src');

        $rules = [
            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Domain'))
                ->should(new NotDependOnTheseNamespaces(
                    'App\Contexts\*\Infrastructure',
                    'Illuminate\Database\Eloquent',
                    'Illuminate\Http',
                ))
                ->because('Domain must be framework-agnostic and cannot know about Eloquent or HTTP'),

            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Application'))
                ->should(new NotDependOnTheseNamespaces(
                    'App\Contexts\*\Infrastructure',
                    'Illuminate\Database\Eloquent',
                ))
                ->because('Application layer orchestrates domain — it must not reach into Infrastructure directly'),
        ];

        $violations = Check::assertArchitectureFor($classSet, ...$rules);

        expect($violations)->toBeEmpty();
    });

── NamingConventionsTest.php ────────────────────────────────────────────────

    <?php

    declare(strict_types=1);

    use Arkitect\ClassSet;
    use Arkitect\CLI\Check;
    use Arkitect\Expression\ForClasses\HaveNameMatching;
    use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
    use Arkitect\Rules\Rule;

    it('command handlers follow naming convention', function (): void {
        $classSet = ClassSet::fromDir(__DIR__ . '/../../src');

        $rules = [
            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Application\Commands'))
                ->andThat(new HaveNameMatching('*Handler'))
                ->should(new HaveNameMatching('*Handler'))
                ->because('Command Handlers must be suffixed with Handler'),

            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Application\Queries'))
                ->andThat(new HaveNameMatching('*Handler'))
                ->should(new HaveNameMatching('*Handler'))
                ->because('Query Handlers must be suffixed with Handler'),

            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Infrastructure\Persistence\Eloquent\Models'))
                ->should(new HaveNameMatching('*EloquentModel'))
                ->because('Eloquent models must be suffixed with EloquentModel to avoid confusion with Domain Entities'),

            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\*\Infrastructure\Persistence\Mappers'))
                ->should(new HaveNameMatching('*Mapper'))
                ->because('Mapper classes must be suffixed with Mapper'),
        ];

        $violations = Check::assertArchitectureFor($classSet, ...$rules);

        expect($violations)->toBeEmpty();
    });

── DependencyRulesTest.php ──────────────────────────────────────────────────

    <?php

    declare(strict_types=1);

    use Arkitect\ClassSet;
    use Arkitect\CLI\Check;
    use Arkitect\Expression\ForClasses\NotDependOnTheseNamespaces;
    use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
    use Arkitect\Rules\Rule;

    it('contexts do not directly import other contexts domain entities', function (): void {
        $classSet = ClassSet::fromDir(__DIR__ . '/../../src/Contexts');

        // Each context's Domain must only use its own domain or Core/Shared/Domain
        // Cross-context communication must go through Contracts/DTOs or IntegrationEvents
        $rules = [
            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\Auth\Domain'))
                ->should(new NotDependOnTheseNamespaces(
                    'App\Contexts\Users\Domain',
                    'App\Contexts\Notifications\Domain',
                ))
                ->because('Bounded Contexts must communicate via Contracts/DTOs or IntegrationEvents, not by importing each other\'s domain'),

            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Contexts\Users\Domain'))
                ->should(new NotDependOnTheseNamespaces(
                    'App\Contexts\Auth\Domain',
                ))
                ->because('Users domain must remain isolated — use Auth Contracts or IntegrationEvents'),
        ];

        $violations = Check::assertArchitectureFor($classSet, ...$rules);

        expect($violations)->toBeEmpty();
    });

── CI Integration (GitHub Actions example) ──────────────────────────────────

    # .github/workflows/architecture.yml
    name: Architecture & Static Analysis

    on: [push, pull_request]

    jobs:
      phpstan:
        name: PHPStan Level 8
        runs-on: ubuntu-latest
        steps:
          - uses: actions/checkout@v4
          - uses: shivammathur/setup-php@v2
            with: { php-version: '8.5' }
          - run: composer install --no-interaction
          - run: ./vendor/bin/phpstan analyse --memory-limit=512M

      architecture:
        name: Architecture Tests (PHPArkitect)
        runs-on: ubuntu-latest
        steps:
          - uses: actions/checkout@v4
          - uses: shivammathur/setup-php@v2
            with: { php-version: '8.5' }
          - run: composer install --no-interaction
          - run: ./vendor/bin/pest tests/Architecture/

── Summary: what each layer can and cannot import ───────────────────────────

    Layer           | Can import                              | Cannot import
    ────────────────┼─────────────────────────────────────────┼──────────────────────────────────────
    Domain          | Core/Shared/Domain, own Value Objects   | Infrastructure, Eloquent, HTTP, Laravel
    Application     | Domain, Core/Shared/Application, DTOs   | Infrastructure, Eloquent
    Infrastructure  | Domain (Ports), Application, Eloquent   | (no restrictions — it's the adapter)
    ────────────────┴─────────────────────────────────────────┴──────────────────────────────────────

    The Mapper lives in Infrastructure and is the ONLY class allowed to import both
    the Domain Entity and the EloquentModel. No other class should ever do this.

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
