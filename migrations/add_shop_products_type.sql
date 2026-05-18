-- Migration: Add shop_products_type column to SHOP table
-- This column was defined in the schema but may not exist in the live database.

ALTER TABLE SHOP ADD (shop_products_type VARCHAR2(500));
