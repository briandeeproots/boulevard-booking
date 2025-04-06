# Boulevard Booking Widget

A WordPress plugin that allows you to easily integrate Boulevard's booking functionality into any element on your WordPress site by assigning specific CSS IDs.

![Boulevard Booking Widget](https://www.joinblvd.com/wp-content/uploads/2023/01/boulevard-logo.png)

## Description

The Boulevard Booking Widget plugin provides a simple way to add Boulevard's self-booking overlay functionality to any element on your WordPress site. By configuring triggers with specific CSS IDs and corresponding JavaScript implementations, you can turn any button, link, or element into a booking trigger.

## Features

- **Easy Integration**: Add Boulevard booking functionality to any element with a simple CSS ID
- **Multiple Triggers**: Create unlimited booking triggers for different services or locations
- **Customizable Implementation**: Full control over the JavaScript implementation for each trigger
- **Code Editor**: Built-in code editor with syntax highlighting for JavaScript
- **Flexible Usage**: Works with buttons, links, or any HTML element
- **Page Builder Compatible**: Works with Elementor, Beaver Builder, Divi, and Gutenberg

## Installation

1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Activate the plugin after installation
5. Go to "Boulevard Booking" in the WordPress admin menu to configure

## Configuration

### Boulevard Initialization Script

If you haven't already added the Boulevard script to your site, you can add it in the "Boulevard Initialization Script" section. This should include the complete script with `<script>` tags.

### Booking Triggers

1. **Create Triggers**: Each trigger needs:
   - A unique ID (e.g., "wrinkles", "facial", "massage")
   - JavaScript implementation (the Boulevard booking widget code)

2. **JavaScript Implementation Format**:
   ```javascript
   blvd.openBookingWidget({ urlParams: { locationId: 'your-location-id', path: '/cart/menu/Services/your-service-id', visitType: 'SELF_VISIT' }});
   ```

3. **Common Parameters**:
   - `locationId`: Your Boulevard location ID (required)
   - `path`: Path to specific category or service
   - `visitType`: Usually set to 'SELF_VISIT'

## Usage

After configuring your triggers, you can use them in your site in several ways:

### As a Button ID

```html
<button id="wrinkles">Book Consultation</button>
```

### As a Link ID

```html
<a href="#" id="wrinkles">Book Now</a>
```

### As a Link Href

```html
<a href="#wrinkles">Book Consultation</a>
```

## Working with Page Builders

You can add CSS IDs to elements in most page builders:

- **Elementor**: Advanced tab > CSS ID field
- **Beaver Builder**: Advanced tab > ID field
- **Divi**: Advanced tab > CSS ID field
- **Gutenberg**: Block settings > Advanced > HTML anchor

## Troubleshooting

If triggers aren't working:

- Make sure the Boulevard script is properly loaded on your site
- Check browser console for errors or messages
- Verify the JavaScript implementation is correct and includes the full function call

## Developer Information

The plugin creates two main files:

1. `boulevard-booking-widget.php` - The main plugin file
2. `js/boulevard-booking.js` - The frontend JavaScript that handles the triggers

The JavaScript file uses jQuery to attach event handlers to elements with the specified IDs and executes the corresponding Boulevard booking code when triggered.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed for integration with [Boulevard](https://www.joinblvd.com/?utm_source=referral&utm_medium=plugin-directory&utm_campaign=boulevard-booking-plugin), the all-in-one scheduling and point of sale platform built for appointment-based businesses.

Built By [Brian Paknoosh](https://brian.lt) 
