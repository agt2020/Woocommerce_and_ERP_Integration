CREATE TRIGGER sg_customer_insert 
    AFTER INSERT ON wp_users
    FOR EACH ROW 
	INSERT INTO sg_customers
	SET id = NEW.ID,
		sg_id = NULL,
		description = NULL,
		date = NOW();