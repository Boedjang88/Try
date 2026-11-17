.PHONY: all dev test health-check migrate seed setup clean

# Default target
all: health-check test

# Boot the entire development stack
dev:
	php artisan erp:health-check
	npm run dev

# Run Automated End-to-End Feature Test Suite
test:
	php artisan test

# Execute ERP System Health Diagnostics
health-check:
	php artisan erp:health-check

# Run Database Migrations and Seeders
migrate:
	php artisan migrate --force

seed:
	php artisan db:seed --force

# Complete First-Time Setup
setup:
	php artisan key:generate || true
	php artisan storage:link || true
	php artisan migrate:fresh --seed --force
	php artisan erp:health-check
	php artisan test
