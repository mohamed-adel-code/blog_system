<?php
// English Language File for the Blog System

$lang = [
    // General Translations
    'all_rights_reserved' => 'All Rights Reserved', // Copyright notice
    'blog_title' => 'My Awesome Blog', // Title of the blog
    'by' => 'By', // Preposition for post author
    'in' => 'in', // Preposition for category
    'no_posts_yet' => 'No posts to display yet.', // Message when no posts are available
    'read_more' => 'Read More &rarr;', // Link text for reading more

    // Navigation
    'dashboard' => 'Dashboard', // Admin dashboard link
    'home' => 'Home', // Home page link
    'login' => 'Login', // Login link
    'logout' => 'Logout', // Logout link
    'manage_categories' => 'Manage Categories', // Manage categories link
    'manage_comments' => 'Manage Comments', // Manage comments link
    'manage_posts' => 'Manage Posts', // Manage posts link
    'manage_users' => 'Manage Users', // Manage users link
    'new_account' => 'Create New Account', // Create new account link
    'register' => 'Register', // Register link

    // Login/Register
    'already_have_account' => 'Already have an account?', // Prompt for existing account
    'confirm_password' => 'Confirm Password', // Confirm password field label
    'dont_have_account' => 'Don\'t have an account?', // Prompt for creating an account
    'email' => 'Email', // Email field label
    'email_exists' => 'Email already exists.', // Error for duplicate email
    'email_required' => 'Email is required.', // Error for missing email
    'invalid_credentials' => 'Invalid username/email or password.', // Error for invalid login credentials
    'invalid_email_format' => 'Invalid email format.', // Error for invalid email format
    'login_failed' => 'Login failed. Incorrect username or password.', // Error for failed login
    'login_success' => 'Login successful!', // Success message for login
    'password' => 'Password', // Password field label
    'password_mismatch' => 'Passwords do not match.', // Error for mismatched passwords
    'password_too_short' => 'Password must be at least 8 characters long', // Error for short password
    'register_success' => 'Registration successful! Please log in.', // Success message for registration
    'username' => 'Username', // Username field label
    'username_exists' => 'Username already exists.', // Error for duplicate username
    'username_or_email' => 'Username or Email', // Label for username or email field

    // Posts
    'add_new_post' => 'Add New Post', // Button to add new post
    'add_post' => 'Add Post', // Button to submit new post
    'category' => 'Category', // Category field label
    'change_image' => 'Change Image (optional)', // Label for changing image
    'confirm_delete_post' => 'Are you sure you want to delete this post?', // Confirmation for deleting post
    'current_image' => 'Current Image:', // Label for current post image
    'delete_post' => 'Delete Post', // Button to delete post
    'draft' => 'Draft', // Draft status
    'edit_post' => 'Edit Post', // Title for editing post
    'error_adding_post' => 'Error adding post.', // Error for adding post
    'error_deleting_post' => 'Error deleting post.', // Error for deleting post
    'error_updating_post' => 'Error updating post.', // Error for updating post
    'image_too_large' => 'Image size is too large (max 5MB)', // Error for large image size
    'image_upload_error' => 'Error uploading image.', // Error for image upload failure
    'invalid_category_id' => 'Invalid category ID', // Error for invalid category ID
    'invalid_post_id' => 'Invalid post ID', // Error for invalid post ID
    'invalid_status' => 'Invalid post status', // Error for invalid post status
    'no_image' => 'No image currently.', // Message when no image exists
    'no_posts_table' => 'No posts currently.', // Message when no posts exist in table
    'post_added_success' => 'Post added successfully!', // Success message for adding post
    'post_content' => 'Post Content', // Post content field label
    'post_content_required' => 'Post content is required.', // Error for missing post content
    'post_deleted_success' => 'Post deleted successfully!', // Success message for deleting post
    'post_image' => 'Post Image', // Post image field label
    'post_not_found' => 'Post not found', // Error for post not found
    'post_title' => 'Post Title', // Post title field label
    'post_title_required' => 'Post title is required.', // Error for missing post title
    'post_title_too_long' => 'Post title is too long (max 255 characters)', // Error for long post title
    'post_updated_success' => 'Post updated successfully!', // Success message for updating post
    'published' => 'Published', // Published status
    'status' => 'Status', // Status field label
    'unauthorized_access' => 'You are not authorized to access this post', // Error for unauthorized post access
    'unsupported_image_format' => 'Unsupported image format.', // Error for unsupported image format
    'update_post' => 'Update Post', // Button to update post

    // Categories
    'add_category' => 'Add Category', // Button to submit new category
    'add_new_category' => 'Add New Category', // Button to add new category
    'categories' => 'Categories', // Categories section title
    'category_description' => 'Description', // Category description field label
    'category_exists' => 'This category already exists.', // Error for duplicate category
    'category_added_success' => 'Category added successfully!', // Success message for adding category
    'category_deleted_success' => 'Category deleted successfully!', // Success message for deleting category
    'category_name' => 'Category Name', // Category name field label
    'category_name_required' => 'Category name is required.', // Error for missing category name
    'category_updated_success' => 'Category updated successfully!', // Success message for updating category
    'confirm_delete_category' => 'Are you sure you want to delete this category?', // Confirmation for deleting category
    'delete_category' => 'Delete Category', // Button to delete category
    'edit_category' => 'Edit Category', // Title for editing category
    'error_adding_category' => 'Error adding category.', // Error for adding category
    'error_deleting_category' => 'Error deleting category.', // Error for deleting category
    'error_updating_category' => 'Error updating category.', // Error for updating category
    'no_categories_yet' => 'No categories currently.', // Message when no categories exist
    'update_category' => 'Update Category', // Button to update category

    // Comments
    'add_comment' => 'Add Comment', // Button to submit comment
    'approve' => 'Approve', // Approve button label
    'author_name' => 'Author Name', // Author name column label
    'comment_added_success' => 'Your comment has been added successfully! It will appear after review.', // Success message for adding comment
    'comment_deleted_success' => 'Comment deleted successfully!', // Success message for deleting comment
    'comment_required' => 'Comment is required.', // Error for missing comment
    'comment_approved_success' => 'Comment approved successfully!', // Success message for approving comment
    'comment_rejected_success' => 'Comment rejected successfully!', // Success message for rejecting comment
    'comments' => 'Comments', // Comments section title
    'confirm_delete_comment' => 'Are you sure you want to delete this comment?', // Confirmation for deleting comment
    'error_adding_comment' => 'Error adding comment.', // Error for adding comment
    'error_deleting_comment' => 'Error deleting comment.', // Error for deleting comment
    'error_updating_comment' => 'Error updating comment.', // Error for updating comment
    'no_comments_yet' => 'No comments yet. Be the first to comment!', // Message when no comments exist
    'reject' => 'Reject', // Reject button label
    'your_comment' => 'Your Comment', // Comment field label
    'your_email' => 'Your Email', // Email field label for comments
    'your_name' => 'Your Name', // Name field label for comments

    // Users
    'admin' => 'Admin', // Admin role
    'author' => 'Author', // Author role
    'cancel' => 'Cancel', // Cancel button label
    'cannot_delete_last_admin' => 'Cannot delete the last admin in the system', // Error for deleting last admin
    'cannot_delete_self' => 'You cannot delete your own account.', // Error for attempting to delete self
    'confirm_delete_user' => 'Are you sure you want to delete this user?', // Confirmation for deleting user
    'delete_user_confirm_message' => 'Are you sure you want to delete the user "%s"?', // Detailed confirmation for deleting user
    'edit_user' => 'Edit User', // Title for editing user
    'error_adding_user' => 'Error adding user', // Error for adding user
    'error_deleting_user' => 'Error deleting user.', // Error for deleting user
    'error_updating_user' => 'Error updating user.', // Error for updating user
    'invalid_role' => 'Invalid user role', // Error for invalid user role
    'invalid_user_id' => 'Invalid user ID', // Error for invalid user ID
    'no_users_yet' => 'No users currently.', // Message when no users exist
    'role' => 'Role', // Role field label
    'update_user' => 'Update User', // Button to update user
    'user' => 'User', // User role
    'user_deleted_success' => 'User deleted successfully!', // Success message for deleting user
    'user_not_found' => 'User not found', // Error for user not found
    'user_updated_success' => 'User updated successfully!', // Success message for updating user
    'username_col' => 'Username', // Username column label
    'username_required' => 'Username is required.', // Error for missing username
    'username_too_long' => 'Username is too long (max 50 characters)', // Error for long username

    // Admin Panel
    'actions' => 'Actions', // Actions column label
    'admin_panel' => 'Admin Panel', // Admin panel title
    'created_at' => 'Created At', // Created at column label
    'delete' => 'Delete', // Delete button label
    'edit' => 'Edit', // Edit button label
    'email_col' => 'Email', // Email column label
    'id' => '#', // ID column label
    'posts' => 'Posts', // Posts section title
    'role_col' => 'Role', // Role column label
    'users' => 'Users', // Users section title

    // Messages
    'error' => 'Error', // Error message type
    'info' => 'Info', // Info message type
    'invalid_csrf_token' => 'Invalid CSRF token, please try again', // Error for invalid CSRF token
    'success' => 'Success', // Success message type
    'warning' => 'Warning', // Warning message type
];