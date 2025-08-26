# Profile System Setup Guide

## Overview

This system allows users to answer stress and mood awareness questions using emojis and save their responses to the database. The answers are displayed on their profile page.

## Features

- ✅ Emoji-based question answers (no words needed)
- ✅ Daily mood tracking
- ✅ User profile display with registration details
- ✅ Database storage of all responses
- ✅ Real-time answer saving with AJAX

## Setup Instructions

### 1. Create Database Tables

Run the setup script to create the necessary database tables:

```
http://localhost/Carepoint/setup_profile_tables.php
```

This will create:

- `profile_questions` table - stores all questions with emoji options
- `user_profile_answers` table - stores user responses

### 2. Access the Profile Page

Navigate to:

```
http://localhost/Carepoint/Myprofile.php
```

You can also specify a user ID:

```
http://localhost/Carepoint/Myprofile.php?user_id=1
```

## How It Works

### Question Types

1. **Emoji Scale** (😴 to 💀) - For stress level questions
2. **Emoji Choice** (😊, 😐, 😔) - For multiple choice questions
3. **Text Input** - For open-ended questions

### Default Questions

1. How stressed do you feel right now? (Emoji scale)
2. How well did you sleep last night? (Emoji choice)
3. Have you felt overwhelmed recently? (Emoji choice)
4. What's your biggest source of stress? (Emoji choice)
5. Have you taken a break for yourself today? (Emoji choice)
6. Do you feel you have someone to talk to? (Emoji choice)
7. How's your energy level right now? (Emoji choice)
8. Have you noticed physical symptoms of stress? (Emoji choice)
9. What's one thing you wish could be different? (Text input)

### User Interface

- Click on emoji buttons to select answers
- Click the ✔ button to save your answer
- Success/error messages appear below each question
- Your profile shows name and email from the register table

## Files Created/Modified

### New Files:

- `Myprofile.php` - Main profile page with questions
- `save_profile_answers.php` - Handles saving answers via AJAX
- `load_profile_data.php` - API to load user data and answers
- `setup_profile_tables.php` - Database setup script
- `create_profile_tables.sql` - SQL commands for manual setup

### Modified Files:

- `Sidebar.html` - Updated profile link to point to PHP version

## Database Schema

### profile_questions

- `id` - Primary key
- `question_text` - The question text
- `question_type` - 'emoji_scale', 'emoji_choice', or 'text'
- `options` - Comma-separated emoji options
- `created_at` - Timestamp

### user_profile_answers

- `id` - Primary key
- `user_id` - References register table
- `question_id` - References profile_questions table
- `answer` - User's response (emoji or text)
- `answer_date` - Date of response
- `created_at` - Timestamp

## Testing

1. Run the setup script
2. Access Myprofile.php
3. Answer questions using emojis
4. Check that answers are saved and displayed
5. Try different user IDs to see different profiles

## Future Enhancements

- Session management for user authentication
- Answer history and trends
- Customizable questions
- Export functionality
- Advanced mood analytics
