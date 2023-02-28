# Blog API Project
## It has multiauthentication system (Superadmin, Admin and user).
## 1. Log In API -  
### - Registered users can login through passport authentication.
### - Two admins are facebook users which can login using socialite through facebook authentication.
## 2. Logout API - 
### Registered users can logout through passport authentication.
## 3. Admin Listing API -
### Superadmin can list all the admins stored in database with a true/false key, is admin facebook user or not. 

# Category crud
## 4. Category heirarchy API - 
### Categories have one root category, categories and subcategories.
## 5. Category Listing API -
### category listing using transformers (available includes).
## 6. Edit category API - 
### Admin can edit categories which uploaded by him. 
## 7. Delete category API -
### Admin can delete categories which has subcategories only.
# Post crud
## 8. Upload post API -
### - Admins can upload post (title - required, category - required, description-required and which files has .jpg, .PNG formats only - optional) using postman.
### - Post uploaded by facebook users will upload on facebook page in background using facebook graph api.
### - Event, Listener and queue will use to upload post on facebook page.  
## 8. Post listing API -
### - Admins can list own posts only.
### - But Superadmin can list all the post strored in database. 
## 9. Edit post API -
### - Admin can update post which uploaded by him only.
### - Post will update on facebook page in background using facebook graph api through event listener and queue.
## 10. Delete post API -
### - Admin can delete post which uploaded by him only.
### - Post will delete on facebook page in background using facebook graph api through event listener and queue.
# Comment crud 
## 11. Post comments API -
### - Users can comment on post and comment will upload on facebook page using facebook graph api through event listener and queue.
## 12. Edit comments API -
### - Users can edit thier comment on post and comment will update on facebook page using facebook graph api through event listener and queue.
## 13. Delete comments API -
### - Users can delete thier comment and comment will delete from facebook page using facebook graph api through event listener and queue.
## 14. Comment list API -
### hasMany relationship
### - Post and comments lisitng using transformer.
### Inverse hasMany relationship
### - Comments and post lisitng using transformer.
# Webhook
## 15. Insert post into database
### - When user will upload post facebook page then post will insert into database in background through zapier webhook.
