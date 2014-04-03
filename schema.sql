--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: invoice_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE invoice_type AS ENUM (
    'invoice',
    'credit_note'
);


ALTER TYPE public.invoice_type OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.clients_id_seq OWNER TO invoices;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: clients; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE clients (
    id bigint DEFAULT nextval('clients_id_seq'::regclass) NOT NULL,
    company_id bigint NOT NULL,
    name character varying(50) NOT NULL,
    street character varying(50) NOT NULL,
    city character varying(50) NOT NULL,
    zip character(5) NOT NULL,
    company_in character(8),
    vat_id character(10),
    email character varying(50),
    phone character varying(50),
    comment text
);


ALTER TABLE public.clients OWNER TO invoices;

--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.companies_id_seq OWNER TO invoices;

--
-- Name: companies; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE companies (
    id bigint DEFAULT nextval('companies_id_seq'::regclass) NOT NULL,
    name character varying(50) NOT NULL,
    street character varying(50) NOT NULL,
    city character varying(50) NOT NULL,
    zip character(5) NOT NULL,
    trade_register character varying(200) NOT NULL,
    company_in character(8) NOT NULL,
    vat_id character(10),
    email character varying(50),
    phone character varying(50),
    website character varying(50),
    bank_account character varying(50),
    comment text
);


ALTER TABLE public.companies OWNER TO invoices;

--
-- Name: invoices_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE invoices_id_seq
    START WITH 1000000000
    INCREMENT BY 1
    MINVALUE 1000000000
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.invoices_id_seq OWNER TO invoices;

--
-- Name: invoices; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE invoices (
    id bigint DEFAULT nextval('invoices_id_seq'::regclass) NOT NULL,
    company_id bigint NOT NULL,
    client_id bigint NOT NULL,
    type invoice_type NOT NULL,
    create_date date NOT NULL,
    end_date date NOT NULL,
    comment text
);


ALTER TABLE public.invoices OWNER TO invoices;

--
-- Name: invoices_products_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE invoices_products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.invoices_products_id_seq OWNER TO invoices;

--
-- Name: invoices_products; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE invoices_products (
    id bigint DEFAULT nextval('invoices_products_id_seq'::regclass) NOT NULL,
    invoice_id bigint NOT NULL,
    product_id bigint NOT NULL,
    price numeric(10,2) NOT NULL,
    tax smallint NOT NULL,
    count smallint NOT NULL,
    warranty smallint NOT NULL
);


ALTER TABLE public.invoices_products OWNER TO invoices;

--
-- Name: managers_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE managers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.managers_id_seq OWNER TO invoices;

--
-- Name: managers; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE managers (
    id bigint DEFAULT nextval('managers_id_seq'::regclass) NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(128) NOT NULL
);


ALTER TABLE public.managers OWNER TO invoices;

--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO invoices;

--
-- Name: payments; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE payments (
    id bigint DEFAULT nextval('payments_id_seq'::regclass) NOT NULL,
    invoice_id bigint NOT NULL,
    amount numeric(10,2) NOT NULL,
    date date NOT NULL,
    comment text
);


ALTER TABLE public.payments OWNER TO invoices;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE permissions (
    company_id bigint NOT NULL,
    manager_id bigint NOT NULL,
    role_company boolean NOT NULL,
    role_permissions boolean NOT NULL,
    role_clients boolean NOT NULL,
    role_invoices boolean NOT NULL,
    role_products boolean NOT NULL,
    role_payments boolean NOT NULL
);


ALTER TABLE public.permissions OWNER TO invoices;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permissions_id_seq OWNER TO invoices;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: invoices
--

CREATE SEQUENCE products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.products_id_seq OWNER TO invoices;

--
-- Name: products; Type: TABLE; Schema: public; Owner: invoices; Tablespace: 
--

CREATE TABLE products (
    id bigint DEFAULT nextval('products_id_seq'::regclass) NOT NULL,
    company_id bigint NOT NULL,
    name character varying(50) NOT NULL,
    count integer,
    price numeric(10,2),
    tax smallint,
    comment text,
    warranty smallint
);


ALTER TABLE public.products OWNER TO invoices;

--
-- Name: v_clients; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_clients AS
 SELECT c.id,
    c.company_id,
    c.name,
    c.street,
    c.city,
    c.zip,
    c.company_in,
    c.vat_id,
    c.email,
    c.phone,
    c.comment,
    c2.name AS company_name,
    p.manager_id
   FROM ((clients c
   JOIN companies c2 ON ((c2.id = c.company_id)))
   LEFT JOIN permissions p ON (((c.company_id = p.company_id) AND (p.role_clients = true))));


ALTER TABLE public.v_clients OWNER TO invoices;

--
-- Name: v_companies; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_companies AS
 SELECT c.id,
    c.name,
    c.street,
    c.city,
    c.zip,
    c.trade_register,
    c.company_in,
    c.vat_id,
    c.email,
    c.phone,
    c.website,
    c.bank_account,
    c.comment,
    p.manager_id
   FROM (companies c
   LEFT JOIN permissions p ON (((c.id = p.company_id) AND (p.role_company = true))));


ALTER TABLE public.v_companies OWNER TO invoices;

--
-- Name: v_invoices; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_invoices AS
 SELECT i.id,
    i.company_id,
    i.client_id,
    i.type,
    i.create_date,
    i.end_date,
    i.comment,
    c.name AS company_name,
    c2.name AS client_name,
    ( SELECT sum(((ip.count)::numeric * ip.price)) AS sum
           FROM invoices_products ip
          WHERE (ip.invoice_id = i.id)) AS amount,
    p.manager_id,
    ( SELECT sum(p2.amount) AS sum
           FROM payments p2
          WHERE (p2.invoice_id = i.id)) AS payed
   FROM (((invoices i
   JOIN companies c ON ((c.id = i.company_id)))
   JOIN clients c2 ON ((c2.id = i.client_id)))
   LEFT JOIN permissions p ON (((i.company_id = p.company_id) AND (p.role_invoices = true))));


ALTER TABLE public.v_invoices OWNER TO invoices;

--
-- Name: v_payments; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_payments AS
 SELECT p.id,
    p.invoice_id,
    p.amount,
    p.date,
    p.comment,
    c.name AS company_name,
    c2.name AS client_name,
    p2.manager_id,
    i.company_id
   FROM ((((payments p
   JOIN invoices i ON ((i.id = p.invoice_id)))
   JOIN companies c ON ((c.id = i.company_id)))
   JOIN clients c2 ON ((c2.id = i.client_id)))
   LEFT JOIN permissions p2 ON (((i.company_id = p2.company_id) AND (p2.role_clients = true))));


ALTER TABLE public.v_payments OWNER TO invoices;

--
-- Name: v_permissions; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_permissions AS
 SELECT p.company_id,
    p.manager_id,
    p.role_company,
    p.role_permissions,
    p.role_clients,
    p.role_invoices,
    p.role_products,
    p.role_payments,
    c.name AS company_name,
    m.username AS manager_name,
    p2.manager_id AS owner_id
   FROM (((permissions p
   LEFT JOIN permissions p2 ON (((p.company_id = p2.company_id) AND (p2.role_permissions = true))))
   JOIN companies c ON ((c.id = p.company_id)))
   JOIN managers m ON ((m.id = p.manager_id)));


ALTER TABLE public.v_permissions OWNER TO invoices;

--
-- Name: v_products; Type: VIEW; Schema: public; Owner: invoices
--

CREATE VIEW v_products AS
 SELECT p.id,
    p.company_id,
    p.name,
    p.count,
    p.price,
    p.tax,
    p.comment,
    c.name AS company_name,
    p2.manager_id,
    p.warranty
   FROM ((products p
   JOIN companies c ON ((c.id = p.company_id)))
   LEFT JOIN permissions p2 ON (((p.company_id = p2.company_id) AND (p2.role_clients = true))));


ALTER TABLE public.v_products OWNER TO invoices;

--
-- Name: clients_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: companies_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY invoices
    ADD CONSTRAINT invoices_pkey PRIMARY KEY (id);


--
-- Name: invoices_products_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY invoices_products
    ADD CONSTRAINT invoices_products_pkey PRIMARY KEY (id);


--
-- Name: managers_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY managers
    ADD CONSTRAINT managers_pkey PRIMARY KEY (id);


--
-- Name: payments_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (company_id, manager_id);


--
-- Name: products_pkey; Type: CONSTRAINT; Schema: public; Owner: invoices; Tablespace: 
--

ALTER TABLE ONLY products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: username_unique; Type: INDEX; Schema: public; Owner: invoices; Tablespace: 
--

CREATE UNIQUE INDEX username_unique ON managers USING btree (username);


--
-- Name: d_companies; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE d_companies AS
    ON DELETE TO v_companies DO INSTEAD  DELETE FROM companies
  WHERE (companies.id = old.id);


--
-- Name: delete; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE delete AS
    ON DELETE TO v_permissions DO INSTEAD  DELETE FROM permissions
  WHERE ((permissions.manager_id = old.manager_id) AND (permissions.company_id = old.company_id));


--
-- Name: delete; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE delete AS
    ON DELETE TO v_clients DO INSTEAD  DELETE FROM clients
  WHERE (clients.id = old.id);


--
-- Name: delete; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE delete AS
    ON DELETE TO v_payments DO INSTEAD  DELETE FROM payments
  WHERE (payments.id = old.id);


--
-- Name: delete; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE delete AS
    ON DELETE TO v_invoices DO INSTEAD  DELETE FROM invoices
  WHERE (invoices.id = old.id);


--
-- Name: delete; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE delete AS
    ON DELETE TO v_products DO INSTEAD  DELETE FROM products
  WHERE (products.id = old.id);


--
-- Name: u_companies; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE u_companies AS
    ON UPDATE TO v_companies DO INSTEAD  UPDATE companies SET name = new.name, street = new.street, city = new.city, zip = new.zip, trade_register = new.trade_register, company_in = new.company_in, vat_id = new.vat_id, email = new.email, phone = new.phone, website = new.website, bank_account = new.bank_account, comment = new.comment
  WHERE (companies.id = old.id);


--
-- Name: update; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE update AS
    ON UPDATE TO v_permissions DO INSTEAD  UPDATE permissions SET role_company = new.role_company, role_permissions = new.role_permissions, role_clients = new.role_clients, role_invoices = new.role_invoices, role_products = new.role_products, role_payments = new.role_payments
  WHERE ((permissions.manager_id = old.manager_id) AND (permissions.company_id = old.company_id));


--
-- Name: update; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE update AS
    ON UPDATE TO v_clients DO INSTEAD  UPDATE clients SET name = new.name, street = new.street, city = new.city, zip = new.zip, company_in = new.company_in, vat_id = new.vat_id, email = new.email, phone = new.phone, comment = new.comment
  WHERE (clients.id = old.id);


--
-- Name: update; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE update AS
    ON UPDATE TO v_payments DO INSTEAD  UPDATE payments SET amount = new.amount, date = new.date, comment = new.comment
  WHERE (payments.id = old.id);


--
-- Name: update; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE update AS
    ON UPDATE TO v_invoices DO INSTEAD  UPDATE invoices SET client_id = new.client_id, type = new.type, create_date = new.create_date, end_date = new.end_date, comment = new.comment
  WHERE (invoices.id = old.id);


--
-- Name: update; Type: RULE; Schema: public; Owner: invoices
--

CREATE RULE update AS
    ON UPDATE TO v_products DO INSTEAD  UPDATE products SET name = new.name, count = new.count, price = new.price, tax = new.tax, comment = new.comment
  WHERE (products.id = old.id);


--
-- Name: client_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY invoices
    ADD CONSTRAINT client_fk FOREIGN KEY (client_id) REFERENCES clients(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT company_fk FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY clients
    ADD CONSTRAINT company_fk FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY invoices
    ADD CONSTRAINT company_fk FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY products
    ADD CONSTRAINT company_fk FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: invoice_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY invoices_products
    ADD CONSTRAINT invoice_fk FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: invoice_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY payments
    ADD CONSTRAINT invoice_fk FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manager_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT manager_fk FOREIGN KEY (manager_id) REFERENCES managers(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: product_fk; Type: FK CONSTRAINT; Schema: public; Owner: invoices
--

ALTER TABLE ONLY invoices_products
    ADD CONSTRAINT product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

