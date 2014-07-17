# WP-IG


Integrate your Instagram account to your WordPress site.

== Description ==

WP-IG enables you to:

1. Display hashtag / user feed using shortcode
2. Importing all of your Instagram photo/video
3. Syncing your future Instagram photo/video to your site
4. Cherry-pick your Instagram photo/video to be posted
5. Embed other people's Instagram photo/video easily

### 1. Browse, Cross-Post, & Embed

After authorizing your WordPress site to your Instagram account, you can browse your feed inside your WordPress dashboard. There are two kind of actions available:

1. You can cross-post your Instagram photo/video to your WordPress site. The cross-posted photo/video will be published using post format "image" and post type stated on settings page (set to "post" by default) under specified category stated on settings page (set to Uncategorized by default).
2. To comply with [Instagram API term of use](http://instagram.com/about/legal/terms/api/), you cannot cross-post other users' photo/video. Instead, you'll be able to embed other users' Instagram photo/video. When you click "Repost This", you will be redirected to WordPress add new post screen with the Instagram photo/video automatically embedded on the editor.

### 2. Import

Simply open the import screen, then click the import button. **All of your Instagram photo/video** will be imported to your WordPress sites.

If you have lots of photo/video on your Instagram account, this task will take quite some time. Please be patient.

### 3. Delete

If for some reason you want to delete your imported Instagram photo/video, you can open the delete screen then click the delete button. **All of your imported Instagram photo/video** will be deleted.

Remember: there's no way to undo this task, so make sure to do this before you actually doing this.

### 5. Sync

You can set WP-IG to sync your Instagram photo/video to your WordPress site. Simply choose yes on the settings screen then WP-IG will check every 5 minute for a new photo/video on your account.

### 4. Embed feed

You can easily embed your Instagram feed. Here are available parameters:

[instagram] - display your own Instagram photo/video
[instagram username=""] - display user's Instagram photo/video
[instagram tag_name=""] - display Instagram photo/video which are posted with certain hashtag

Please note that you can use username and tag_name parameters at the same time. 

Instead of the parameters mentioned above, you can also use

[instagram cache="120"] - Get new result from API every 2 minutes
[instagram ignore_cache="true"] - Ignore the cache and always get the result from API

== Installation ==

1. Put wp-ig directory on your wp-content/plugins directory
2. Activate the plugin
3. You will be prompted with Settings screen. Follow the instruction on the settings screen which are:
4. Create an [Instagram client app here](http://instagram.com/developer/clients/register/). IMPORTANT: Remember to use Redirect URI given by WP-IG upon your Instagram client app registration.
5. After registering your Instagram client app, you will get client ID and client secret of your Instagram client app. Copy and then  paste it on the WP-IG settings screen. Save the settings.
6. After saving client ID and secret key, you'll see an oAuth authorization link. Click it to authorize your WordPress site to your Instagram account.
7. That's that. 