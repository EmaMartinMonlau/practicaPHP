FROM php:8.5-apache-bookworm

# 2. Instalamos las herramientas necesarias y activamos los drivers (pdo_pgsql y pdo_mysql) para las Bases de Datos
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# 3. Copiamos nuestra configuración de Apache (la que creamos en el paso 2) al interior del contenedor
COPY docker/apache/blend.conf /etc/apache2/sites-available/blend.conf

# 4. Apagamos el sitio por defecto de Apache y encendemos el nuestro
RUN a2dissite 000-default \
    && a2ensite blend \
    && echo 'ServerName blend.test' > /etc/apache2/conf-available/blend-servername.conf \
    && a2enconf blend-servername

# 5. Establecemos la carpeta de trabajo
WORKDIR /var/www/html