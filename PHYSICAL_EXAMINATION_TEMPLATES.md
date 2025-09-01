# Physical Examination Template Module

## Overview
This module provides a comprehensive template system for Physical Examination (P.E.) documentation in the appointment form. It allows doctors and administrators to quickly insert standardized P.E. templates and create custom templates for specific use cases.

## Features

### Pre-built Templates
The system comes with 10 pre-built templates covering common medical scenarios:

1. **Normal Adult** - Standard adult physical examination
2. **Cardiovascular** - Focused cardiovascular assessment
3. **Respiratory** - Respiratory system evaluation
4. **Abdominal** - Abdominal examination findings
5. **Neurological** - Neurological assessment
6. **Musculoskeletal** - Joint and muscle evaluation
7. **Pediatric** - Child-specific examination
8. **Geriatric** - Elderly patient assessment
9. **Pre-Employment** - Employment physical examination
10. **Post-Operative** - Post-surgical evaluation

### Custom Templates
- Create and save custom templates
- Templates are stored locally in the browser
- Custom templates can be deleted individually
- Template names must be unique

## Usage

### Inserting Templates
1. Navigate to the **Diagnosis** tab in the appointment form
2. Locate the **P.E.** (Physical Examination) section
3. Click on any template button to insert it into the P.E. textarea
4. If there's existing content, the template will be appended with proper spacing

### Creating Custom Templates
1. Click the **"Custom Template"** button
2. Fill in the template name (must be unique)
3. Enter the template content in the textarea
4. Click **"Save Template"** to add it to your collection

### Managing Templates
- **Default templates** (1-10) cannot be deleted
- **Custom templates** (11+) can be deleted using the small red delete button
- All custom templates are automatically saved to localStorage
- Templates persist between browser sessions

## Template Content Format
Templates use a structured format with clear sections:

```
Section: findings
Section: findings
Section: findings
```

Example:
```
Gait: normal
Arms: Normal ROM
Spine: non tender, full ROM
Heart: regular rate and rhythm, no murmurs
Lungs: clear to auscultation bilaterally
```

## Technical Implementation

### Frontend Components
- **Template Buttons**: Quick access to all available templates
- **Custom Template Dialog**: Form for creating new templates
- **Template Management**: Delete functionality for custom templates
- **Local Storage**: Persistence of custom templates

### Data Structure
```javascript
{
  id: number,
  name: string,
  content: string
}
```

### Methods
- `insertPETemplate(content)` - Inserts template into P.E. field
- `saveCustomTemplate()` - Saves new custom template
- `deleteTemplate(templateId)` - Removes custom template
- `saveTemplatesToStorage()` - Persists to localStorage
- `loadTemplatesFromStorage()` - Loads from localStorage

## Styling
- Default templates: Blue buttons
- Custom templates: Green buttons with delete icons
- Responsive design with proper spacing
- Hover effects for better user experience

## Browser Compatibility
- Uses localStorage for template persistence
- Graceful fallback if localStorage is unavailable
- Compatible with modern browsers

## Future Enhancements
- Database storage for templates
- Template sharing between users
- Template categories and search
- Import/export functionality
- Template versioning

