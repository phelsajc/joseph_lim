# PE Templates Database Setup

## Overview
This guide explains how to set up the database storage for Physical Examination (P.E.) templates, replacing the localStorage implementation with a proper database solution.

## Database Setup

### 1. Run the Migration
```bash
php artisan migrate
```

This will create the `pe_templates` table with the following structure:
- `id` - Primary key
- `name` - Template name (unique)
- `content` - Template content
- `type` - Template type (default/custom)
- `created_by` - User ID who created the template
- `is_active` - Whether the template is active
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### 2. Run the Seeder
```bash
php artisan db:seed --class=PeTemplatesSeeder
```

This will populate the database with 10 default templates:
1. Normal Adult
2. Cardiovascular
3. Respiratory
4. Abdominal
5. Neurological
6. Musculoskeletal
7. Pediatric
8. Geriatric
9. Pre-Employment
10. Post-Operative

## API Endpoints

The following API endpoints are available for managing PE templates:

### GET `/api/pe-templates`
- **Description**: Get all active templates
- **Authentication**: Required
- **Response**: List of all templates with their content

### GET `/api/pe-templates/type/{type}`
- **Description**: Get templates by type (default/custom)
- **Authentication**: Required
- **Parameters**: `type` - Template type
- **Response**: List of templates of the specified type

### POST `/api/pe-templates`
- **Description**: Create a new custom template
- **Authentication**: Required
- **Body**: 
  ```json
  {
    "name": "Template Name",
    "content": "Template content..."
  }
  ```
- **Response**: Created template data

### PUT `/api/pe-templates/{id}`
- **Description**: Update an existing template
- **Authentication**: Required
- **Parameters**: `id` - Template ID
- **Body**: Same as POST
- **Response**: Updated template data

### DELETE `/api/pe-templates/{id}`
- **Description**: Delete a template
- **Authentication**: Required
- **Parameters**: `id` - Template ID
- **Response**: Success message

### PATCH `/api/pe-templates/{id}/toggle-status`
- **Description**: Toggle template active status (admin only)
- **Authentication**: Required (admin role)
- **Parameters**: `id` - Template ID
- **Response**: Updated template data

## Security Features

### Access Control
- **Default templates**: Cannot be modified or deleted
- **Custom templates**: Can only be modified/deleted by the creator or admin
- **Status toggle**: Only admins can toggle template active status

### Validation
- Template names must be unique
- Template content is required and limited to 5000 characters
- Template names are limited to 255 characters

## Frontend Changes

### Template Loading
- Templates are now loaded from the database on component creation
- No more localStorage dependency
- Templates persist across sessions and users

### Template Management
- Custom templates can be created and saved to the database
- Custom templates can be deleted (with confirmation)
- Template changes are immediately reflected for all users

### Error Handling
- Proper error messages for API failures
- Fallback to empty template list if database is unavailable
- User-friendly success/error notifications

## Benefits of Database Storage

1. **Persistence**: Templates are saved permanently and survive browser sessions
2. **Sharing**: Templates can be shared across different users and devices
3. **Backup**: Templates are backed up with the database
4. **Scalability**: Can handle large numbers of templates efficiently
5. **Security**: Proper access control and validation
6. **Audit Trail**: Track who created/modified templates and when

## Troubleshooting

### Common Issues

1. **Migration fails**: Ensure Laravel is properly configured and database connection is working
2. **Seeder fails**: Check if the `pe_templates` table exists and is empty
3. **API errors**: Verify authentication middleware is working and user has proper permissions
4. **Templates not loading**: Check browser console for API errors and verify backend is running

### Debug Commands

```bash
# Check migration status
php artisan migrate:status

# Rollback and re-run migrations
php artisan migrate:rollback
php artisan migrate

# Check database connection
php artisan tinker
DB::connection()->getPdo();
```

## Future Enhancements

1. **Template Categories**: Group templates by medical specialty
2. **Template Sharing**: Allow users to share templates with specific users
3. **Template Versioning**: Track changes to templates over time
4. **Template Import/Export**: Bulk import/export functionality
5. **Template Analytics**: Track template usage statistics
6. **Template Approval Workflow**: Require admin approval for new templates

