CREATE TRIGGER sg_invoice_insert 
    AFTER INSERT ON wp_wc_order_stats
    FOR EACH ROW 
	INSERT INTO sg_invoices
	SET id = NEW.order_id,
		sg_id = NULL,
		description = NULL,
		date = NOW();