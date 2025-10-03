# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress LMS (Learning Management System) for **École Internationale des Affaires (EIA)**, built on WordPress with LearnPress as the core LMS plugin. The project includes a custom plugin (**EIA LMS Core**) and a custom theme (**eia-theme**) that extend LearnPress with advanced features.

**Environment**: Laragon (Windows), URL: `http://eia-wp.test/`
**Database**: `eia-wp` (MySQL, root user, no password)
**WordPress Admin**: `/wp-admin/`

## Core Architecture

### Custom Plugin: EIA LMS Core
Located: `wp-content/plugins/eia-lms-core/`

**Main Plugin File**: `eia-lms-core.php`
- Singleton pattern with `EIA_LMS_Core::get_instance()`
- Loads 7 core module classes from `includes/`
- Creates custom database tables on activation
- Requires LearnPress to be active

**Core Modules** (all singletons in `includes/`):
1. **class-roles-capabilities.php**: Role management (Student, Instructor, LMS Manager)
2. **class-course-builder.php**: Advanced course creation
3. **class-quiz-extended.php**: Extended quiz functionality
4. **class-gradebook.php**: Grading and assessment system
5. **class-reports.php**: Analytics and reporting
6. **class-seeder.php**: Demo data generation
7. **class-assignments.php**: Assignment submission and grading system

### Custom Theme: EIA Theme
Located: `wp-content/themes/eia-theme/`

**Key Templates**:
- `single-lp_course.php`: Course detail page (minimal layout, admin bar visible)
- `single-lp_assignment.php`: Assignment submission page
- `page.php`: Generic page template with admin bar
- `functions.php`: Core theme functions including:
  - Admin bar customization (lines 530-686)
  - Course enrollment handler (lines 698-770)
  - Student dashboard shortcode `[eia_my_courses]` (lines 835-1100)

### Custom Database Tables

**LearnPress Tables** (core plugin):
- `wp_learnpress_user_items`: Course enrollments and progress
- `wp_learnpress_sections`: Course curriculum sections
- `wp_learnpress_section_items`: Lessons/quizzes within sections

**Custom Tables** (EIA LMS Core):
- `wp_eia_gradebook`: Manual grading entries
- `wp_eia_course_analytics`: Learning analytics events
- `wp_eia_assignment_submissions`: Assignment submissions with grades and feedback

## Key Features Implemented

### 1. Role-Based Access Control
Three main roles with custom capabilities:
- **Student**: Course access, quiz taking, assignment submission
- **Instructor**: Course management, grading, student monitoring
- **LMS Manager**: Full LMS access (no plugin/theme management)

Admin bar colors:
- Students: Green (#10B981)
- Instructors: Orange (#F59E0B)
- Admins: Blue (#2D4FB3)

### 2. Course Single Page
Custom fullwidth design with:
- Hidden header/footer (only admin bar visible)
- Curriculum sidebar (sections → lessons)
- Main content area with course info
- Enrollment button for non-enrolled students
- Direct database queries for curriculum (bypasses LearnPress endpoint issues)

### 3. Student Dashboard
URL: `/mes-cours/` (created page with `[eia_my_courses]` shortcode)

Displays:
- Quick stats cards (courses enrolled, assignments, completed courses)
- "Mes Devoirs" section: Assignment cards with status badges
- "Mes Cours" section: Course cards with progress bars

### 4. Assignment System
**Custom Post Type**: `lp_assignment`

**Workflow**:
1. Instructor creates assignment (LearnPress > Devoirs)
2. Configure: due date, max grade, submission type (file/text/both)
3. Associate with course
4. Student submits via `[eia_assignment_submit id="X"]`
5. Instructor grades via `[eia_assignment_submissions id="X"]`

**Submission Types**:
- File upload (PDF, DOC, DOCX, TXT, JPG, PNG, ZIP)
- Online text editor
- Both combined

**Status Badges**:
- 🟠 À faire: Not submitted, within deadline
- 🔴 Dépassé: Overdue
- 🔵 Soumis: Submitted, awaiting grade
- 🟢 Noté: Graded with score displayed

## Development Commands

### Database Table Creation
Run these utility scripts via browser (one-time setup):
```
http://eia-wp.test/create-assignments-table.php
http://eia-wp.test/create-my-courses-page.php
http://eia-wp.test/fix-learnpress-pages.php
```

### Flush Rewrite Rules
After registering new post types or changing permalinks:
```
http://eia-wp.test/flush-rewrite-rules.php
```

### Demo Data Seeder
Access via: **EIA LMS > Seeder** in WordPress admin

Generates:
- 5 instructors: `formateur_X_Y@eia-demo.sn`
- 20 students: `etudiant_X_Y@eia-demo.sn`
- 10 courses with lessons and quizzes
- Random enrollments

**Universal password**: `password123`

### Testing Accounts
See `.claude/DEMO_CREDENTIALS.md` for complete list.

Quick test account:
- Email: `etudiant_abdou_2@eia-demo.sn`
- Password: `password123`

### Debug/Diagnostic Scripts
Located in root directory (prefix: `check-`, `debug-`, `verify-`):
- `check-user-capabilities.php`: View user roles and capabilities
- `debug-profile-url.php`: Check LearnPress profile URLs
- `verify-sections.php`: Inspect course curriculum structure
- `enroll-student.php`: Bulk enroll students for testing

## Important Architectural Patterns

### Direct Database Queries vs LearnPress API
Due to LearnPress endpoint/rewrite rule issues, **direct wpdb queries** are used for:
- Course curriculum retrieval (sections/lessons)
- Enrollment checking
- Assignment submissions

Example pattern:
```php
global $wpdb;
$sections = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}learnpress_sections WHERE section_course_id = %d",
    $course_id
));
```

### AJAX Handlers
Located in module classes, registered with:
```php
add_action('wp_ajax_eia_submit_assignment', array($this, 'handle_submission'));
```

JavaScript files use:
```javascript
$.ajax({
    url: eiaAssignments.ajaxurl,
    data: { action: 'eia_submit_assignment', nonce: eiaAssignments.nonce }
});
```

### Shortcode Pattern
All shortcodes return buffered output:
```php
function eia_my_courses_shortcode() {
    ob_start();
    // ... template code
    return ob_get_clean();
}
add_shortcode('eia_my_courses', 'eia_my_courses_shortcode');
```

Templates loaded from: `wp-content/plugins/eia-lms-core/templates/`

## Custom Modifications to Core Behavior

### Admin Bar on Frontend
Theme templates explicitly show admin bar for logged-in users:
```php
show_admin_bar(true);
```

CSS ensures visibility:
```css
#wpadminbar {
    display: block !important;
    z-index: 99999 !important;
}
body.admin-bar { margin-top: 32px !important; }
```

### Course Enrollment Flow
Custom handler in `functions.php` (line 698):
1. Form with nonce on course page
2. POST to `template_redirect` hook
3. Direct insert to `wp_learnpress_user_items` table
4. Redirect with success message

### "Mes Cours" Page vs LearnPress Profile
LearnPress profile URLs (`/lp-profile/username/courses/`) had routing issues.

**Solution**: Created WordPress page at `/mes-cours/` with custom shortcode that:
- Queries enrolled courses directly from database
- Renders custom dashboard with assignments integration
- Bypasses LearnPress endpoint system entirely

## File Organization

```
wp-content/
├── plugins/
│   └── eia-lms-core/
│       ├── eia-lms-core.php (main plugin file)
│       ├── includes/ (7 module classes)
│       ├── templates/ (assignment UI templates)
│       ├── assets/
│       │   ├── js/ (assignments.js)
│       │   └── css/ (assignments.css)
│       └── includes/admin/ (page-roles-permissions.php)
└── themes/
    └── eia-theme/
        ├── functions.php (1100+ lines of customizations)
        ├── single-lp_course.php (course page)
        ├── single-lp_assignment.php (assignment page)
        ├── page.php (generic page template)
        ├── learnpress/
        │   └── content-single-course.php (course content template)
        └── assets/css/course-single.css
```

## Technical Specifications Reference

See `.claude/specs_wordpress_lms.txt` for:
- Complete project requirements (based on Moodle feature set)
- Planned modules and development phases
- Budget and timeline estimates
- Stack: LearnPress + BuddyPress + GamiPress + custom development

## Common Troubleshooting

### Assignment submissions not saving
1. Check table exists: `wp_eia_assignment_submissions`
2. Run: `http://eia-wp.test/create-assignments-table.php`
3. Verify AJAX nonce in browser console

### Course curriculum not displaying
- Sections must exist in `wp_learnpress_sections`
- Items linked via `wp_learnpress_section_items`
- Use `verify-sections.php` to debug

### "Mes Cours" redirects to homepage
- Page must exist with slug `mes-cours`
- Must contain shortcode `[eia_my_courses]`
- Run `create-my-courses-page.php` if missing

### Enrollment button not working
- Check nonce verification in `functions.php:698`
- Verify user is logged in
- Confirm course is published

## LearnPress Integration Notes

**LearnPress Version**: Compatible with 4.x
**Required LearnPress Add-ons**: None (all features custom-built)

**LearnPress Core Tables Used**:
- `wp_learnpress_user_items`: Enrollments (item_type = 'lp_course')
- `wp_learnpress_sections`: Course structure
- `wp_learnpress_section_items`: Curriculum items

**Custom Post Types from LearnPress**:
- `lp_course`: Courses
- `lp_lesson`: Lessons
- `lp_quiz`: Quizzes

**Custom Post Types Added**:
- `lp_assignment`: Assignments (by EIA LMS Core)

## Documentation Files

Located in `.claude/`:
- `ASSIGNMENTS_GUIDE.md`: Complete assignment system documentation
- `DEMO_CREDENTIALS.md`: Test accounts and passwords
- `SEEDER_GUIDE.md`: Data seeder usage
- `TEST_CHECKLIST.md`: QA testing procedures
- `COURSE_SINGLE_PAGE_FINAL.md`: Course page implementation notes
- `PLUGINS_STATUS.md`: Plugin inventory

## Color Palette

Primary colors used throughout:
- EIA Blue: `#2D4FB3`
- EIA Orange: `#F59E0B`
- Success Green: `#10B981`
- Info Blue: `#3B82F6`
- Danger Red: `#EF4444`
- Warning Yellow: `#F59E0B`

## Security Considerations

- All AJAX handlers use nonce verification
- File upload validation (type + size)
- Capability checks for admin functions
- Direct SQL uses `$wpdb->prepare()` for parameterization
- User input sanitized with `sanitize_text_field()`, `wp_kses_post()`

## Git Workflow

### Repository
**GitHub**: `https://github.com/simonet85/eia-wp.git`
**Main Branch**: `master`

### Initial Setup (if not already configured)
```bash
cd C:\laragon\www\eia-wp
git remote add origin https://github.com/simonet85/eia-wp.git
git branch -M master
```

### Commit Guidelines

**Commit Message Format**:
```bash
# Feature commits
git commit -m "feat: Add assignment submission system with file upload"

# Bug fixes
git commit -m "fix: Course curriculum not displaying sections"

# Documentation
git commit -m "docs: Add CLAUDE.md with architecture guide"
```

**Message Types**: `feat:`, `fix:`, `docs:`, `style:`, `refactor:`, `perf:`, `test:`, `chore:`

### Staging Strategy

**Files to Always Commit**:
- `wp-content/plugins/eia-lms-core/` (custom plugin)
- `wp-content/themes/eia-theme/` (custom theme)
- `CLAUDE.md` and `.claude/*.md` (documentation)

**Files to Never Commit**:
- `wp-config.php` (database credentials)
- `.htaccess` (server-specific)
- `wp-content/uploads/` (media files)
- `check-*.php`, `debug-*.php` (diagnostic scripts)
- WordPress core files (`wp-admin/`, `wp-includes/`)

### Common Commands

```bash
# Stage plugin/theme changes
git add wp-content/plugins/eia-lms-core/
git add wp-content/themes/eia-theme/

# Commit with detailed message
git commit -m "feat: Complete assignment submission system

- Add custom post type lp_assignment
- Student submission form (file + text)
- Instructor grading interface
- Integration with student dashboard"

# Push to GitHub
git push origin master

# Pull latest changes
git pull origin master
```

### Feature Branch Workflow

```bash
# Create feature branch
git checkout -b feature/assignment-system

# Push to remote
git push -u origin feature/assignment-system

# Merge when complete
git checkout master
git merge feature/assignment-system
git push origin master
```

### Database Synchronization

**Important**: Git does not sync database changes.

After pulling code with schema changes:
1. Run: `http://eia-wp.test/create-assignments-table.php`
2. Or deactivate/reactivate plugin in WordPress admin

Share database dumps separately (not via Git).

### Recommended .gitignore

```gitignore
# WordPress core
/wp-admin/
/wp-includes/
/wp-*.php
!/wp-content/

# Configuration
wp-config.php
.htaccess

# Uploads
wp-content/uploads/
wp-content/cache/

# Plugins (track only custom)
wp-content/plugins/*
!wp-content/plugins/eia-lms-core/

# Themes (track only custom)
wp-content/themes/*
!wp-content/themes/eia-theme/

# Temporary
*.log
.DS_Store
NUL
check-*.php
debug-*.php
verify-*.php
```

## Future Development Priorities

Based on specs (not yet implemented):
1. Calendar integration for assignment deadlines
2. Notification center (email + in-app)
3. Chat system (private + group)
4. Advanced search with filters
5. Student peer networking
6. Quiz result analytics with attempt history
7. Surveys and polls system

Refer to `.claude/specs_wordpress_lms.txt` lines 135-152 for complete dashboard requirements.
