=== KAPOW Image Recommendation ===
Contributors: brightminded
Donate link: https://www.paypal.com/donate/?cmd=_s-xclick&hosted_button_id=EWRE3GK578M2W
Tags: media, images, photos, recommendation, unsplash
Requires at least: 4.7
Tested up to: 5.8
Stable tag: 1.0.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

KAPOW Image Recommendation plugin can analyse the text from your posts and pages and return relevant, freely usable images from unsplash.com.

== Description ==

If you are a digital content author, KAPOW Image Recomendation, developed by [BrightMinded](https://brightminded.com) and [Dash](https://www.builtbybright.com/dash/), can help you speed up the process of finding relevant, free-to-use images and photos to complement and enhance your copy.

This is because KAPOW can read and analyse your copy, infer what the important concepts are and perform targeted searches on various platforms of freely usable images like [Unsplash](https://unsplash.com) and [Pixabay](https://pixabay.com/).

Features include:

*   Analyse the entire post/page copy by simply clicking on the KAPOW media button.
*   Select only parts of your page or post to focus the scope of the analysis. 
*   Selection works in both *Text* and *Visual* mode.
*   Allow for more or less creative freedom when searching images by configuring basic settings of the analyser like degree of topic relevance. 

The KAPOW image recommendation plugin comes with a default API Key for the KAPOW Image Recommendation service. With the default API Key, images are sourced exclusively from [Unsplash](https://unsplash.com). [Contact us](mailto:kapow@brightminded.com) to enquire about other image sources.

## Limitations ##

* KAPOW currently will not work with the *Gutenberg* editor and will require the classic editor to be enabled instead.
* For now, the KAPOW Image Recommendation plugin only works with English text.

## Support ##

*   If you have issues or questions about the KAPOW Image Recommendation Plugin, visit the plugin's forum.

== Frequently Asked Questions ==

= What does the Threshold slider do in the KAPOW admin settings =

Through the *Threshold* slider you can specify the range of related concepts that you want to allow for the search. Specifically, KAPOW will analyse your copy and identify the core topics. However, a topic can be related to other concepts. For example, if you are talking about knights this could be related to history, literature or even chess. The larger the value you set for *Threshold* the closer KAPOW remains to the main topics. If you want KAPOW to explore further afield just lower the *Threshold* value. This typically results in a larger number of images returned. Thus, if KAPOW doesn't immediately find images, try lowering the *Threshold*. 

= What does the Minimum Topic Score (MTS) slider do in the KAPOW admin settings =

KAPOW analyses your text to identify the core topics. KAPOW expresses the confidence it has about the relevance of a topic in the form of a score. The higher this score the more confident KAPOW is about the relevance of a topic. If you set a high value to the *MTS* parameter, KAPOW will dismiss any topics that score lower than that value. Sometimes however, you may purposely want to allow topics that score lower in order to create more variety and surprise in the images returned. The lower the *MTS* value, the more topics are allowed and therefore, typically, more images will be returned. As above, if KAPOW does not immediately find images, try to lower the *MTS*. 

= What does the Number of Images per Keyword (NIK) do in the KAPOW admin settings =

It allows you to set the approximate number of image suggestions for each topic and concept extracted from your text.

== Screenshots ==

== Changelog ==

= 1.0.1 =
* This version fixes the text selection bug whereby inserting an image replaced the selected text.

== Upgrade Notice ==


