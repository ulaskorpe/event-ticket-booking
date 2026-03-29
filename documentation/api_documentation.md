Document Management System API Documentation
Authentication Endpoints
Register User
Endpoint: POST /{version}/auth/resgister
Description: Registers a new user account in the system
Request Body: SignUpRequest (validated)
Response: Success message with HTTP status 201
Authentication: Not required
Login
Endpoint: POST /{version}/auth/login
Description: Authenticates a user and generates access token
Request Body: AuthRequest (validated)
Response: AuthResponse containing access token
Authentication: Not required
Refresh Token
Endpoint: POST /{version}/auth/refresh-token
Description: Refreshes the access token using the refresh token from cookies
Request: HTTP request containing refresh token cookie
Response: New tokens set in cookies and response body
Authentication: Requires valid refresh token
Logout
Endpoint: POST /{version}/auth/logout
Description: Logs out the user by invalidating the refresh token
Request: HTTP request containing the refresh token
Response: Success message
Authentication: Required

Forgot Password
Endpoint: POST /{version}/auth/forgot-password
Description: Initiates password reset process by sending reset link to user's email
Request Body: ForgotPasswordRequest (validated)
Response: Success message
Authentication: Not required
Reset Password
Endpoint: POST /{version}/auth/reset-password
Description: Resets user password using the token from reset link
Request Body: ResetPasswordRequest (validated)
Response: Success message
Authentication: Not required

Branch Management Endpoints
Create Branch
Endpoint: POST /{version}/branch
Description: Creates a new branch in the system
Request Body: CreateBranchDto (validated)
Response: BranchResponseDto with HTTP status 201
Required Role: SUPER_ADMIN
Retrieve Branch
Endpoint: GET /{version}/branch/{branchId}
Description: Retrieves a specific branch by its ID
Path Variable: branchId (UUID)
Response: BranchResponseDto
Required Role: SUPER_ADMIN

Retrieve All Branches
Endpoint: GET /{version}/branch
Description: Retrieves all branches in the system
Response: List of BranchSummaryDto
Required Role: SUPER_ADMIN
Update Branch
Endpoint: PATCH /{version}/branch/{branchId}
Description: Updates an existing branch
Path Variable: branchId (UUID)
Request Body: BranchUpdateDto (validated)
Response: BranchResponseDto
Required Role: SUPER_ADMIN

Department Management Endpoints
Create Department
Endpoint: POST /{version}/department
Description: Creates a new department in the system
Request Body: CreateDepartmentDto (validated)
Response: DepartmentResponseDto with HTTP status 201
Required Roles: SUPER_ADMIN, BRANCH_ADMIN
Retrieve Department
Endpoint: GET /{version}/department/{departmentId}
Description: Retrieves a specific department by its ID
Path Variable: departmentId (UUID)
Response: DepartmentResponseDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN

Retrieve All Departments
Endpoint: GET /{version}/department
Description: Retrieves all departments accessible to the user
Response: List of DepartmentSummaryDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN
Update Department
Endpoint: PATCH /{version}/department/{departmentId}
Description: Updates an existing department
Path Variable: departmentId (UUID)
Request Body: DepartmentUpdateDto (validated)
Response: DepartmentResponseDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN

Document Management Endpoints
Upload Document
Endpoint: POST /{version}/document
Description: Initiates document upload by generating Cloudinary upload parameters
Request Body: CreateDocumentDto (validated)
Response: CloudinaryUploadParamsResponse
Authentication: Required
Webhook Notification
Endpoint: POST /{version}/document/upload/webhook
Description: Handles webhook notifications from Cloudinary after upload completion
Request Body: Map payload from Cloudinary
Response: None (void)
Authentication: Not required (external webhook)

Document Type Management Endpoints
Create Document Type
Endpoint: POST /{version}/document-type
Description: Creates a new document type
Request Body: CreateDocumentTypeDto (validated)
Response: DocumentTypeResponseDto with HTTP status 201
Authentication: Required
Retrieve Document Type
Endpoint: GET /{version}/document-type/{typeId}
Description: Retrieves a specific document type by its ID
Path Variable: typeId (UUID)
Response: DocumentTypeResponseDto
Authentication: Required
Retrieve All Document Types
Endpoint: GET /{version}/document-type
Description: Retrieves all document types in the system
Response: List of DocumentTypeResponseDto
Authentication: Required

Folder Management Endpoints
Create Folder
Endpoint: POST /{version}/folder
Description: Creates a new folder in the system
Request Body: CreateFolderDto (validated)
Response: FolderResponseDto with HTTP status 201
Authentication: Required

Retrieve Folder
Endpoint: GET /{version}/folder/{folderId}
Description: Retrieves a specific folder by its ID
Path Variable: folderId (UUID)
Response: FolderResponseDto
Authentication: Required
Retrieve All Folders
Endpoint: GET /{version}/folder
Description: Retrieves all folders accessible to the user
Response: List of FolderSummaryDto
Authentication: Required
Update Folder
Endpoint: PATCH /{version}/folder/{folderId}
Description: Updates an existing folder
Path Variable: folderId (UUID)
Request Body: FolderUpdateDto (validated)
Response: FolderResponseDto
Authentication: Required

Permission Management Endpoints
Create Permission
Endpoint: POST /{version}/permission
Description: Creates a new permission for a user or role
Request Body: PermissionRequest
Response: PermissionResponseDto with HTTP status 201
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN


Role Management Endpoints
Create Role
Endpoint: POST /{version}/role
Description: Creates a new role in the system
Request Body: RoleRequest (validated)
Response: RoleResponse with HTTP status 201
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN

Shelf Management Endpoints
Create Shelf
Endpoint: POST /{version}/shelf
Description: Creates a new shelf in the system
Request Body: CreateShelfDto (validated)
Response: ShelfResponseDto with HTTP status 201
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN
Retrieve Shelf
Endpoint: GET /{version}/shelf/{shelfId}
Description: Retrieves a specific shelf by its ID
Path Variable: shelfId (UUID)
Response: ShelfResponseDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN
Retrieve All Shelves
Endpoint: GET /{version}/shelf
Description: Retrieves all shelves accessible to the user
Response: List of ShelfSummaryDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN

Update Shelf
Endpoint: PATCH /{version}/shelf/{shelfId}
Description: Updates an existing shelf
Path Variable: shelfId (UUID)
Request Body: ShelfUpdateDto (validated)
Response: ShelfResponseDto
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN

User Management Endpoints
Get Current User Info
Endpoint: GET /{version}/user/me
Description: Retrieves information about the currently authenticated user
Response: UserDTO
Authentication: Required

User Role Assignment Endpoints
Assign User Role
Endpoint: POST /{version}/user-role
Description: Assigns a role to a user
Request Body: CreateUserRoleDto (validated)
Response: UserRoleResponse with HTTP status 201
Required Roles: SUPER_ADMIN, BRANCH_ADMIN, DEPARTMENT_ADMIN
Notes
{version}: Represents the application version from configuration
UUID: All ID path variables are UUID format
Validated: Request bodies are validated using Jakarta validation annotations
Authentication: Most endpoints require JWT authentication
Role-based access: Different endpoints have different role requirements as specified`