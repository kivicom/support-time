# Use an official PHP runtime as a base image
FROM php:8.3.2-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the contents of the PHP code to the container
COPY . /var/www/html

# Expose the port your web server will run on
EXPOSE 80

# Start the Apache web server
CMD ["apache2-foreground"]