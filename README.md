# i-Connect
# WP HTTP Dynamic Request Plugin

## Description

This plugin is a WordPress utility that dynamically constructs HTTP requests based on user-defined settings. It's designed to interact with external APIs, allowing the retrieval and update of product data in a WooCommerce shop. This includes fetching product details, updating product stock levels, and handling product categories.

## Features

- Dynamically constructs HTTP requests based on user-defined settings
- Interacts with external APIs to fetch and update product data
- Provides an interface in the WordPress dashboard for defining request types and executing them
- Includes capabilities for inserting or updating products, including their respective categories
- Provides functionality to delete existing products

## Installation

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'HTTP Requests' menu in the WordPress dashboard to configure your request settings.

## Usage

After activating the plugin, a new option 'HTTP Requests' will appear in your WordPress dashboard. Here, you can define your request types.

Each request type requires a unique name, along with details for the service and SQL name. Once defined, you can execute these requests from the dropdown menu on the 'Update Products' page.

The plugin provides two main functionalities - 'Update Products' and 'Update Stock Levels'. The former fetches product data from an external source, and either inserts new products or updates existing ones in your WooCommerce shop. The latter allows you to update the stock levels of your products.

## Frequently Asked Questions

**What is a request type and how do I create one?**

A request type is a user-defined setting that includes details for constructing a specific HTTP request. You can create a new request type from the 'HTTP Requests' menu in your WordPress dashboard.

**How does the plugin interact with WooCommerce?**

The plugin fetches product data from an external API and uses it to insert or update products in your WooCommerce shop. It can also update product stock levels.

## Changelog

### 1.0
- Initial release.

## Upgrade Notice

### 1.0
- Initial release.
