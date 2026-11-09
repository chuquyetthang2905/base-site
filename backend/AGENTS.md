# base-site — AI rules (SLIM, auto-loaded every request)
# Full code templates: docs/conventions.md (read only when implementing a feature)

# STACK
# Laravel ^13 (11+/12 compatible), PHP 8.5, Docker (nginx, MySQL 8.4, Redis 7, Mailpit, queue + scheduler workers)
# TZ Asia/Ho_Chi_Minh. Auth = JWT (php-open-source-saver/jwt-auth), guard auth:api. NOT Sanctum/Passport.
# MUST use PHP 8.5 features: constructor promotion, readonly classes, enums, match, strict types/type-hints.
# Do not install new packages without asking.

# ARCHITECTURE: Modular Action-Based (DDD-lite). NO classic MVC, NO "Service" classes for business logic.
# One-way flow, no layer may be skipped:
#   FormRequest (authorize + validate) -> Controller -> DTO -> Single Action (logic + events) -> Model / Repository
#
# app/Domains/[Module]/{Actions,DTOs,Enums,Events,Exceptions,Listeners,Models,Repositories}   <- business core
# app/Http/{Controllers/Api/V1/[Module],Requests/[Module],Resources/[Module],Middleware}  + app/Support (DomainException, RefreshTokenCookie)  <- delivery layer
# app/Providers/DomainServiceProvider.php  <- ALL repository bindings + Event=>Listener maps (add a section per module)

# FormRequest: owns authorization + validation. Controllers never call $request->validate().
#   Exposes toDto(): XxxDTO (maps validated() -> DTO).
# DTO: `final readonly class`, typed promoted props, static fromValidated(array). NEVER pass raw arrays to Actions.
# Controller: `final`, SKINNY. Only: FormRequest -> $request->toDto() -> $action->execute($dto) -> Resource via ApiResponseTrait.
#   No if/else business logic, no DB/Eloquent, no try/catch (DomainException renders itself), Actions injected per method.
# Action: `final readonly class XxxAction`, ONE public method execute(), exactly one responsibility.
#   ALL business-rule checks live here. May call other Actions. Throws DomainException subclasses for rule violations.
#   Dispatches Events. Must not import Request / return JsonResponse / set cookies.
# Database: Eloquent Model directly in Action for basic CRUD. Repository (Interface + Concrete, same folder, bound in
#   DomainServiceProvider) REQUIRED for complex queries, joins, eager-load sets, or Redis cache. Actions never build complex queries.
# Async: Events dispatched from Actions that trigger mail / AI analysis / reports / slow work => their Listener MUST
#   implement ShouldQueue (Redis queue). Laravel only queues listeners, so ShouldQueue goes on the Listener.
#   Listeners live in Domains/[Module]/Listeners and are registered in DomainServiceProvider (no auto-discovery there).

# "Create feature X" => ALWAYS generate the full set, no placeholders, no logic merged into the Controller:
#   1 FormRequest + 1 DTO + 1 Action + 1 Controller method + Resource (+ Model/Migration/Repository/Event/Listener/Exception
#   as needed) + route + Feature test.

# NAMING: XxxRequest, XxxDTO, VerbNounAction (RegisterUserAction), XxxResource, XxxRepository(+Interface),
#   Past-tense events (UserRegistered), Listener = imperative (SendWelcomeEmail). Route names dot.notation. DB snake_case.

# API RESPONSE (ApiResponseTrait only; never response()->json in controllers):
#   success: { success, message, data, meta? }  error: { success, message, data: null, errors? }
#   helpers: success/created/error/validationError/unauthenticated/forbidden/notFound
# AUTH: access JWT TTL 15 min; refresh token = HttpOnly cookie (path /api/v1/auth), rotation + theft detection via family_id.
#   Do not alter refresh/logout mechanics unless asked. Roles via RoleName enum; route middleware 'role:admin'.
