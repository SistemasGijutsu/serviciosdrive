#!/bin/bash

###############################################################################
# Script de Backup de Base de Datos - ServiciosDrive
# 
# Uso: ./backup-database.sh
# 
# Este script crea un respaldo de la base de datos con fecha y hora
# y mantiene solo los últimos 30 días de respaldos
###############################################################################

# Configuración - EDITAR CON TUS CREDENCIALES DE PRODUCCIÓN
DB_USER="nome1978"
DB_PASS="S1**Sar0619-0208188**1"
DB_NAME="serviciosdrive_db"
DB_HOST="localhost"

# Directorio donde se guardarán los backups
BACKUP_DIR="/backups/serviciosdrive"

# Crear directorio de backups si no existe
mkdir -p "$BACKUP_DIR"

# Obtener fecha y hora actual
FECHA=$(date +"%Y%m%d_%H%M%S")

# Nombre del archivo de backup
BACKUP_FILE="$BACKUP_DIR/serviciosdrive_${FECHA}.sql"

# Realizar el backup
echo "Iniciando backup de la base de datos..."
echo "Archivo: $BACKUP_FILE"

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"

# Verificar si el backup fue exitoso
if [ $? -eq 0 ]; then
    echo "✅ Backup completado exitosamente"
    
    # Comprimir el backup
    gzip "$BACKUP_FILE"
    echo "✅ Backup comprimido: ${BACKUP_FILE}.gz"
    
    # Calcular tamaño
    SIZE=$(du -h "${BACKUP_FILE}.gz" | cut -f1)
    echo "📊 Tamaño del backup: $SIZE"
    
    # Eliminar backups antiguos (mantener solo últimos 30 días)
    echo "🧹 Limpiando backups antiguos (>30 días)..."
    find "$BACKUP_DIR" -name "serviciosdrive_*.sql.gz" -mtime +30 -delete
    
    # Listar backups disponibles
    echo ""
    echo "📁 Backups disponibles:"
    ls -lh "$BACKUP_DIR"
    
    echo ""
    echo "✅ Proceso completado"
else
    echo "❌ Error al crear el backup"
    exit 1
fi
