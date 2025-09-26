#!/bin/bash

echo "📋 SCRIPT DE DEBUGGING PARA BLOGS - TIEMPO REAL"
echo "=============================================="
echo ""
echo "Este script te ayudará a ver los logs en tiempo real mientras pruebas los blogs problemáticos."
echo ""
echo "🚀 Instrucciones:"
echo "1. Ejecuta este script en una ventana de terminal"
echo "2. En otra ventana/navegador, intenta acceder a los blogs problemáticos"
echo "3. Los logs aparecerán aquí en tiempo real"
echo ""
echo "📍 Ruta del archivo de logs: storage/logs/laravel.log"
echo ""
echo "Presiona Ctrl+C para salir"
echo ""
echo "=== MONITOREANDO LOGS ==="

# Navegar al directorio del backend
cd /home/u268804017/domains/back.contigo-voy.com/public_html

# Seguir los logs en tiempo real
tail -f storage/logs/laravel.log | grep -E "(BÚSQUEDA BLOG|Blog.*encontrado|ERROR|showbyIdBlog)"
