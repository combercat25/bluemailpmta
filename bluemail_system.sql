--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.10
-- Dumped by pg_dump version 9.6.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: admin; Type: SCHEMA; Schema: -; Owner: admin
--

CREATE SCHEMA admin;


ALTER SCHEMA admin OWNER TO admin;

--
-- Name: production; Type: SCHEMA; Schema: -; Owner: admin
--

CREATE SCHEMA production;


ALTER SCHEMA production OWNER TO admin;

--
-- Name: stats; Type: SCHEMA; Schema: -; Owner: admin
--

CREATE SCHEMA stats;


ALTER SCHEMA stats OWNER TO admin;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: application_roles; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.application_roles (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.application_roles OWNER TO admin;

--
-- Name: blacklist; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.blacklist (
    id integer NOT NULL,
    email text NOT NULL
);


ALTER TABLE admin.blacklist OWNER TO admin;

--
-- Name: bounce_clean_proccesses; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.bounce_clean_proccesses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    list text NOT NULL,
    status character varying(20) NOT NULL,
    progress text NOT NULL,
    hard_bounce integer NOT NULL,
    clean integer NOT NULL,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone
);


ALTER TABLE admin.bounce_clean_proccesses OWNER TO admin;

--
-- Name: data_lists; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.data_lists (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    isp_id integer NOT NULL,
    flag character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text,
    status_id integer DEFAULT 1 NOT NULL
);


ALTER TABLE admin.data_lists OWNER TO admin;

--
-- Name: data_types; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.data_types (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.data_types OWNER TO admin;

--
-- Name: domains; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.domains (
    id integer NOT NULL,
    status_id integer NOT NULL,
    ip_id integer NOT NULL,
    value text NOT NULL,
    domain_status character varying(20) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.domains OWNER TO admin;

--
-- Name: headers; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.headers (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    type character varying(100) DEFAULT NULL::character varying,
    value text
);


ALTER TABLE admin.headers OWNER TO admin;

--
-- Name: ips; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.ips (
    id integer NOT NULL,
    status_id integer NOT NULL,
    server_id integer NOT NULL,
    value character varying(100) NOT NULL,
    rdns character varying(100) DEFAULT NULL::character varying,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.ips OWNER TO admin;

--
-- Name: isps; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.isps (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text
);


ALTER TABLE admin.isps OWNER TO admin;

--
-- Name: offer_creatives; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.offer_creatives (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.offer_creatives OWNER TO admin;

--
-- Name: offer_links; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.offer_links (
    id integer NOT NULL,
    status_id integer NOT NULL,
    creative_id integer NOT NULL,
    value text NOT NULL,
    type character varying(20) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.offer_links OWNER TO admin;

--
-- Name: offer_names; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.offer_names (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.offer_names OWNER TO admin;

--
-- Name: offer_subjects; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.offer_subjects (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.offer_subjects OWNER TO admin;

--
-- Name: offers; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.offers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    sponsor_id integer NOT NULL,
    production_id integer NOT NULL,
    campaign_id integer NOT NULL,
    vertical_id integer NOT NULL,
    name text NOT NULL,
    flag text NOT NULL,
    description text,
    rate character varying(20) DEFAULT NULL::character varying,
    launch_date date NOT NULL,
    expiring_date date NOT NULL,
    rules text,
    epc character varying(20) DEFAULT NULL::character varying,
    suppression_list text,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text,
    key character varying(10)
);


ALTER TABLE admin.offers OWNER TO admin;

--
-- Name: proccesses; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.proccesses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    type text NOT NULL,
    status character varying(20) NOT NULL,
    progress text NOT NULL,
    data text,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone
);


ALTER TABLE admin.proccesses OWNER TO admin;

--
-- Name: seq_id_application_roles; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_application_roles
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_application_roles OWNER TO admin;

--
-- Name: seq_id_blacklist; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_blacklist
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_blacklist OWNER TO admin;

--
-- Name: seq_id_bounce_clean_proccesses; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_bounce_clean_proccesses
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_bounce_clean_proccesses OWNER TO admin;

--
-- Name: seq_id_data_lists; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_data_lists
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_data_lists OWNER TO admin;

--
-- Name: seq_id_data_types; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_data_types
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_data_types OWNER TO admin;

--
-- Name: seq_id_domains; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_domains
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_domains OWNER TO admin;

--
-- Name: seq_id_headers; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_headers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_headers OWNER TO admin;

--
-- Name: seq_id_ips; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_ips
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_ips OWNER TO admin;

--
-- Name: seq_id_isps; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_isps
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_isps OWNER TO admin;

--
-- Name: seq_id_offer_creatives; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_offer_creatives
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_offer_creatives OWNER TO admin;

--
-- Name: seq_id_offer_links; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_offer_links
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_offer_links OWNER TO admin;

--
-- Name: seq_id_offer_names; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_offer_names
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_offer_names OWNER TO admin;

--
-- Name: seq_id_offer_subjects; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_offer_subjects
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_offer_subjects OWNER TO admin;

--
-- Name: seq_id_offers; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_offers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_offers OWNER TO admin;

--
-- Name: seq_id_proccesses; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_proccesses
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_proccesses OWNER TO admin;

--
-- Name: seq_id_server_providers; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_server_providers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_server_providers OWNER TO admin;

--
-- Name: seq_id_server_types; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_server_types
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_server_types OWNER TO admin;

--
-- Name: seq_id_servers; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_servers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_servers OWNER TO admin;

--
-- Name: seq_id_sponsors; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_sponsors
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_sponsors OWNER TO admin;

--
-- Name: seq_id_status; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_status
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_status OWNER TO admin;

--
-- Name: seq_id_suppression_proccesses; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_suppression_proccesses
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_suppression_proccesses OWNER TO admin;

--
-- Name: seq_id_users; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_users
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_users OWNER TO admin;

--
-- Name: seq_id_verticals; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_verticals
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_verticals OWNER TO admin;

--
-- Name: seq_id_vmtas; Type: SEQUENCE; Schema: admin; Owner: admin
--

CREATE SEQUENCE admin.seq_id_vmtas
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE admin.seq_id_vmtas OWNER TO admin;

--
-- Name: server_providers; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.server_providers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    website character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.server_providers OWNER TO admin;

--
-- Name: server_types; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.server_types (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.server_types OWNER TO admin;

--
-- Name: servers; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.servers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    provider_id integer NOT NULL,
    server_type_id integer NOT NULL,
    name character varying(100) NOT NULL,
    host_name character varying(100) NOT NULL,
    main_ip character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    ssh_port integer,
    authorized_users text,
    expiration_date date,
    server_type integer
);


ALTER TABLE admin.servers OWNER TO admin;

--
-- Name: sponsors; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.sponsors (
    id integer NOT NULL,
    status_id integer NOT NULL,
    affiliate_id integer NOT NULL,
    name character varying(20) NOT NULL,
    website text NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    api_key text,
    api_url text,
    api_type text,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.sponsors OWNER TO admin;

--
-- Name: status; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.status (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.status OWNER TO admin;

--
-- Name: suppression_proccesses; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.suppression_proccesses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    sponsor_id integer NOT NULL,
    offer_id integer NOT NULL,
    status character varying(20) NOT NULL,
    progress text NOT NULL,
    emails_found integer NOT NULL,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone
);


ALTER TABLE admin.suppression_proccesses OWNER TO admin;

--
-- Name: users; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.users (
    id integer NOT NULL,
    status_id integer NOT NULL,
    application_role_id integer NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    telephone character varying(20) NOT NULL,
    email character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.users OWNER TO admin;

--
-- Name: verticals; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.verticals (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE admin.verticals OWNER TO admin;

--
-- Name: vmtas; Type: TABLE; Schema: admin; Owner: admin
--

CREATE TABLE admin.vmtas (
    id integer NOT NULL,
    status_id integer NOT NULL,
    server_id integer NOT NULL,
    ip_id integer NOT NULL,
    name character varying(50) NOT NULL,
    type character varying(50) NOT NULL,
    ip_value character varying(100) DEFAULT NULL::character varying,
    domain character varying(100) DEFAULT NULL::character varying,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    username character varying(100),
    password character varying(100),
    smtphost character varying(100)
);


ALTER TABLE admin.vmtas OWNER TO admin;

--
-- Name: drop_ips; Type: TABLE; Schema: production; Owner: admin
--

CREATE TABLE production.drop_ips (
    id integer NOT NULL,
    server_id integer NOT NULL,
    isp_id integer,
    drop_id integer NOT NULL,
    ip_id integer NOT NULL,
    drop_date timestamp without time zone NOT NULL,
    total_sent integer,
    delivered integer,
    bounced integer
);


ALTER TABLE production.drop_ips OWNER TO admin;

--
-- Name: drops; Type: TABLE; Schema: production; Owner: admin
--

CREATE TABLE production.drops (
    id integer NOT NULL,
    user_id integer NOT NULL,
    server_id integer NOT NULL,
    isp_id integer,
    status character varying(20) NOT NULL,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone,
    total_emails integer NOT NULL,
    sent_progress integer,
    offer_id integer NOT NULL,
    offer_from_name_id integer NOT NULL,
    offer_subject_id integer NOT NULL,
    recipients_emails text,
    pids text,
    header text,
    creative_id integer NOT NULL,
    lists text,
    post_data text NOT NULL
);


ALTER TABLE production.drops OWNER TO admin;

--
-- Name: seq_id_drop_ips; Type: SEQUENCE; Schema: production; Owner: admin
--

CREATE SEQUENCE production.seq_id_drop_ips
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE production.seq_id_drop_ips OWNER TO admin;

--
-- Name: seq_id_drops; Type: SEQUENCE; Schema: production; Owner: admin
--

CREATE SEQUENCE production.seq_id_drops
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE production.seq_id_drops OWNER TO admin;

--
-- Name: clicks; Type: TABLE; Schema: stats; Owner: admin
--

CREATE TABLE stats.clicks (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE stats.clicks OWNER TO admin;

--
-- Name: leads; Type: TABLE; Schema: stats; Owner: admin
--

CREATE TABLE stats.leads (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    rate character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE stats.leads OWNER TO admin;

--
-- Name: opens; Type: TABLE; Schema: stats; Owner: admin
--

CREATE TABLE stats.opens (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE stats.opens OWNER TO admin;

--
-- Name: seq_id_clicks; Type: SEQUENCE; Schema: stats; Owner: admin
--

CREATE SEQUENCE stats.seq_id_clicks
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE stats.seq_id_clicks OWNER TO admin;

--
-- Name: seq_id_leads; Type: SEQUENCE; Schema: stats; Owner: admin
--

CREATE SEQUENCE stats.seq_id_leads
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE stats.seq_id_leads OWNER TO admin;

--
-- Name: seq_id_opens; Type: SEQUENCE; Schema: stats; Owner: admin
--

CREATE SEQUENCE stats.seq_id_opens
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE stats.seq_id_opens OWNER TO admin;

--
-- Name: seq_id_unsubs; Type: SEQUENCE; Schema: stats; Owner: admin
--

CREATE SEQUENCE stats.seq_id_unsubs
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE stats.seq_id_unsubs OWNER TO admin;

--
-- Name: unsubs; Type: TABLE; Schema: stats; Owner: admin
--

CREATE TABLE stats.unsubs (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    type character varying(20) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    message text,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE stats.unsubs OWNER TO admin;

--
-- Data for Name: application_roles; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.application_roles VALUES (1, 1, 'Administrator', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.application_roles VALUES (2, 1, 'Mailer', 1, 1, '2017-03-02', '2017-03-02');


--
-- Data for Name: blacklist; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: bounce_clean_proccesses; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.bounce_clean_proccesses VALUES (38, 1, 'gmail.fresh_us_ggggg_1', 'completed', '100%', 0, 0, '2018-11-30 14:36:19', '2018-11-30 09:37:23.336');


--
-- Data for Name: data_lists; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: data_types; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.data_types VALUES (1, 1, 'Fresh', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (2, 1, 'Clean', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (3, 1, 'Openers', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (4, 1, 'Clickers', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (5, 1, 'Leads', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (6, 1, 'Unsubscribers', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.data_types VALUES (7, 1, 'Seeds', 1, 1, '2017-03-02', '2017-03-02');


--
-- Data for Name: domains; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: headers; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: ips; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: isps; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: offer_creatives; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: offer_links; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: offer_names; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: offer_subjects; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: offers; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: proccesses; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Name: seq_id_application_roles; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_application_roles', 2, true);


--
-- Name: seq_id_blacklist; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_blacklist', 17320, true);


--
-- Name: seq_id_bounce_clean_proccesses; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_bounce_clean_proccesses', 38, true);


--
-- Name: seq_id_data_lists; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_data_lists', 204, true);


--
-- Name: seq_id_data_types; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_data_types', 7, true);


--
-- Name: seq_id_domains; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_domains', 116, true);


--
-- Name: seq_id_headers; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_headers', 3, true);


--
-- Name: seq_id_ips; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_ips', 5161, true);


--
-- Name: seq_id_isps; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_isps', 20, true);


--
-- Name: seq_id_offer_creatives; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_offer_creatives', 616, true);


--
-- Name: seq_id_offer_links; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_offer_links', 1230, true);


--
-- Name: seq_id_offer_names; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_offer_names', 846, true);


--
-- Name: seq_id_offer_subjects; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_offer_subjects', 2216, true);


--
-- Name: seq_id_offers; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_offers', 78, true);


--
-- Name: seq_id_proccesses; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_proccesses', 1, true);


--
-- Name: seq_id_server_providers; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_server_providers', 17, true);


--
-- Name: seq_id_server_types; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_server_types', 3, true);


--
-- Name: seq_id_servers; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_servers', 82, true);


--
-- Name: seq_id_sponsors; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_sponsors', 17, true);


--
-- Name: seq_id_status; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_status', 2, true);


--
-- Name: seq_id_suppression_proccesses; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_suppression_proccesses', 38, true);


--
-- Name: seq_id_users; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_users', 19, true);


--
-- Name: seq_id_verticals; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_verticals', 37, true);


--
-- Name: seq_id_vmtas; Type: SEQUENCE SET; Schema: admin; Owner: admin
--

SELECT pg_catalog.setval('admin.seq_id_vmtas', 115, true);


--
-- Data for Name: server_providers; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: server_types; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.server_types VALUES (2, 1, 'VPS Server', 1, 1, '2017-05-12', '2017-05-12');


--
-- Data for Name: servers; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: sponsors; Type: TABLE DATA; Schema: admin; Owner: admin
--



--
-- Data for Name: status; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.status VALUES (1, 'Activated', 1, 1, '2017-03-02', '2017-03-02');
INSERT INTO admin.status VALUES (2, 'Desactivated', 1, 1, '2017-03-02', '2017-03-02');


--
-- Data for Name: suppression_proccesses; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.suppression_proccesses VALUES (37, 1, 17, 78, 'completed', '100%', 817, '2018-11-30 14:12:34', '2018-11-30 09:13:09.488');
INSERT INTO admin.suppression_proccesses VALUES (38, 1, 17, 78, 'completed', '100%', 833, '2018-11-30 14:20:57', '2018-11-30 09:21:07.666');


--
-- Data for Name: users; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.users VALUES (1, 1, 1, 'master', 'master', '', '', 'manone', 'b3c3e644d3ad11462cda8761bd4306e5', 1, 1, '2017-02-27', '2018-01-29');


--
-- Data for Name: verticals; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.verticals VALUES (6, 1, 'Health/Beauty', 1, 1, '2017-10-13', '2017-10-13');
INSERT INTO admin.verticals VALUES (7, 1, 'Health/Wellness', 1, 1, '2017-10-24', '2017-10-24');
INSERT INTO admin.verticals VALUES (8, 1, 'CPC', 1, 1, '2017-10-25', '2017-10-25');
INSERT INTO admin.verticals VALUES (9, 1, 'Misc', 1, 1, '2017-11-03', '2017-11-03');
INSERT INTO admin.verticals VALUES (10, 1, 'Debt', 1, 1, '2017-11-03', '2017-11-03');
INSERT INTO admin.verticals VALUES (11, 1, 'Consumer Awareness', 1, 1, '2017-11-07', '2017-11-07');
INSERT INTO admin.verticals VALUES (12, 1, 'Home Warranty', 1, 1, '2017-11-07', '2017-11-07');
INSERT INTO admin.verticals VALUES (13, 1, 'Mortgage Loan/Refinancing', 1, 1, '2017-11-07', '2017-11-07');
INSERT INTO admin.verticals VALUES (14, 1, 'Astrology / Psychic', 1, 1, '2017-11-08', '2017-11-08');
INSERT INTO admin.verticals VALUES (15, 1, 'Health Insurance', 1, 1, '2017-11-08', '2017-11-08');
INSERT INTO admin.verticals VALUES (16, 1, 'Seasonal', 1, 1, '2017-11-08', '2017-11-08');
INSERT INTO admin.verticals VALUES (17, 1, 'Auto Warranty', 1, 1, '2017-11-08', '2017-11-08');
INSERT INTO admin.verticals VALUES (18, 1, 'Promo', 1, 1, '2017-11-08', '2017-11-08');
INSERT INTO admin.verticals VALUES (19, 1, 'BizOp', 1, 1, '2018-02-01', '2018-02-01');
INSERT INTO admin.verticals VALUES (20, 1, 'Promos', 1, 1, '2018-02-01', '2018-02-01');
INSERT INTO admin.verticals VALUES (21, 1, 'Finance', 19, 19, '2018-02-02', '2018-02-02');
INSERT INTO admin.verticals VALUES (22, 1, 'Retail / Survival', 19, 19, '2018-02-02', '2018-02-02');
INSERT INTO admin.verticals VALUES (23, 1, 'Dental', 19, 19, '2018-02-02', '2018-02-02');
INSERT INTO admin.verticals VALUES (24, 1, 'Credit Cards', 19, 19, '2018-02-02', '2018-02-02');
INSERT INTO admin.verticals VALUES (25, 1, 'Diet', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (26, 1, 'Payday Loan', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (27, 1, 'Solar', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (28, 1, 'Credit Report', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (29, 1, 'Tax Settlement', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (30, 1, 'Personal Loan', 1, 1, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (31, 1, 'Rent To Own', 19, 19, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (32, 1, 'Medical/Dental/Vision', 19, 19, '2018-02-05', '2018-02-05');
INSERT INTO admin.verticals VALUES (33, 1, 'Display Traffic', 1, 1, '2018-02-06', '2018-02-06');
INSERT INTO admin.verticals VALUES (34, 1, 'Health and Wellness', 1, 1, '2018-02-06', '2018-02-06');
INSERT INTO admin.verticals VALUES (35, 1, 'Email Newsletter', 1, 1, '2018-02-07', '2018-02-07');
INSERT INTO admin.verticals VALUES (36, 1, 'AU/NZ Accepted', 1, 1, '2018-02-16', '2018-02-16');
INSERT INTO admin.verticals VALUES (37, 1, 'ASOTV', 1, 1, '2018-11-30', '2018-11-30');


--
-- Data for Name: vmtas; Type: TABLE DATA; Schema: admin; Owner: admin
--

INSERT INTO admin.vmtas VALUES (109, 1, 82, 5155, 'vmta_d_5155_172.106.88.131', 'Default', '172.106.88.131', 'sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (110, 1, 82, 5156, 'vmta_default_5156', 'Default', '2602:ffc5:30::1:2149', 'xjext.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (111, 1, 82, 5157, 'vmta_default_5157', 'Default', '2602:ffc5:30::1:3866', 'ydxad.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (112, 1, 82, 5158, 'vmta_default_5158', 'Default', '2602:ffc5:30::1:85a4', 'pgocp.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (113, 1, 82, 5159, 'vmta_default_5159', 'Default', '2602:ffc5:30::1:ceba', 'ivw.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (114, 1, 82, 5160, 'vmta_default_5160', 'Default', '2602:ffc5:30::1:d548', 'eeoir.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);
INSERT INTO admin.vmtas VALUES (115, 1, 82, 5161, 'vmta_default_5161', 'Default', '2602:ffc5:30::1:e8cf', 'ofv.sdcsdsd.com', 1, 1, '2018-11-30', '2018-11-30', NULL, NULL, NULL);


--
-- Data for Name: drop_ips; Type: TABLE DATA; Schema: production; Owner: admin
--

INSERT INTO production.drop_ips VALUES (157255, 82, 20, 20038, 5155, '2018-11-30 09:29:03.363', 10245, 0, 0);


--
-- Data for Name: drops; Type: TABLE DATA; Schema: production; Owner: admin
--

INSERT INTO production.drops VALUES (20038, 1, 82, 20, 'completed', '2018-11-30 09:29:03.328', '2018-11-30 09:34:07.643', 10245, 10245, 78, 845, 2215, 'mohareparation@gmail.com', '/var/bluemail/tmp/pickups/server_82_H0nb30Aivl0RxkJfM0u6/drop_status_82', 'TUlNRS1WZXJzaW9uOiAxLjANCkZyb206IFtmcm9tX25hbWVdIDxbZnJvbV9lbWFpbF0+DQpTdWJqZWN0OiBbc3ViamVjdF0NClJlcGx5LVRvOiBbcmVwbHlfdG9dDQpSZWNlaXZlZDogW3JlY2VpdmVkXQ0KVG86IFt0b10NCkNvbnRlbnQtVHJhbnNmZXItRW5jb2Rpbmc6IFtjb250ZW50X3RyYW5zZmVyX2VuY29kaW5nXQ0KQ29udGVudC1UeXBlOiBbY29udGVudF90eXBlXTsgY2hhcnNldD0iW2NoYXJzZXRdIg0KRGF0ZTogW21haWxfZGF0ZV0=', 614, 'gmail.fresh_us_ggggg_1', 'eyJzZXJ2ZXJzIjpbIjgyIl0sInNlbGVjdGVkLXZtdGFzIjpbIjgyfDEwOSJdLCJ2bXRhcyI6IiIsImVtYWlscy1zcGxpdC10eXBlIjoidm10YXMiLCJ2bXRhcy1lbWFpbHMtcHJvY2Nlc3MiOiJ2bXRhcy1yb3RhdGlvbiIsInZtdGFzLXJvdGF0aW9uIjoiMSIsImJhdGNoIjoiMTAiLCJ4LWRlbGF5IjoiMSIsInNwb25zb3IiOiIxNyIsIm9mZmVyIjoiNzgiLCJjcmVhdGl2ZSI6IjYxNCIsImZyb20tbmFtZS1pZCI6Ijg0NSIsImZyb20tbmFtZS10ZXh0IjoiPT9VVEYtOD9CP1JuSmxaVVpzWVhOb2JHbG5hSFE9Pz0iLCJzdWJqZWN0LWlkIjoiMjIxNSIsInN1YmplY3QtdGV4dCI6Ij0/VVRGLTg/Qj9XVzkxY2lCR1VrVkZJRlJ2Y21Ob0lGUmhZM1JwWTJGc0lFWnNZWE5vYkdsbmFIUWdMU0FvSkRJNUxqazFJRkpsZEdGcGJDaz0/PSIsImZyb20tZW1haWwiOiJmcm9tQFtkb21haW5dIiwicmV0dXJuLXBhdGgiOiJyZXR1cm5AW2RvbWFpbl0iLCJyZXBseS10byI6InJlcGx5QFtkb21haW5dIiwidG8iOiJbZW1haWxdIiwiYm91bmNlLWVtYWlsIjoiYm91bmNlQFtkb21haW5dIiwicmVjZWl2ZWQiOiJmcm9tIFtkb21haW5dIChbaXBdKSBieSBbZG9tYWluXSBpZCBbYW4xMl0gZm9yIDxbZW1haWxdPjsgW21haWxfZGF0ZV0gKGVudmVsb3BlLWZyb20gPFtyZXR1cm5fcGF0aF0+IiwiY29udGVudC10eXBlIjoidGV4dFwvaHRtbCIsImNoYXJzZXQiOiJVVEYtOCIsImNvbnRlbnQtdHJhbnNmZXItZW5jb2RpbmciOiI3Yml0IiwiaGVhZGVycyI6WyJNSU1FLVZlcnNpb246IDEuMFxyXG5Gcm9tOiBbZnJvbV9uYW1lXSA8W2Zyb21fZW1haWxdPlxyXG5TdWJqZWN0OiBbc3ViamVjdF1cclxuUmVwbHktVG86IFtyZXBseV90b11cclxuUmVjZWl2ZWQ6IFtyZWNlaXZlZF1cclxuVG86IFt0b11cclxuQ29udGVudC1UcmFuc2Zlci1FbmNvZGluZzogW2NvbnRlbnRfdHJhbnNmZXJfZW5jb2RpbmddXHJcbkNvbnRlbnQtVHlwZTogW2NvbnRlbnRfdHlwZV07IGNoYXJzZXQ9XCJbY2hhcnNldF1cIlxyXG5EYXRlOiBbbWFpbF9kYXRlXSJdLCJib2R5IjoiRmVsbG93IFBhdHJpb3QsPGJyIFwvPlxyXG48YnIgXC8+XHJcblxyXG5cclxuVGhlIFRvcmNoIFRhY3RpY2FsIFN1cnZpdmFsaXN0IEZsYXNobGlnaHQgaXMgYWJzb2x1dGVseSBGUkVFIGlmIHlvdSBncmFiIHlvdXJzIDxhIGhyZWY9XCJodHRwOlwvXC8weGFjNmE1ODgzXC90dHR0XC9mbHNoLnBocFwiPnJpZ2h0IG5vdy48XC9hPjxiciBcLz48YnIgXC8+XHJcbjxiciBcLz5cclxuXHJcbn4gUG93ZXJmdWwgMTAwMCBMdW1lbnMgb2YgYmxpbmRpbmcgbGlnaHQuPGJyIFwvPlxyXG5cclxuPGJyIFwvPlxyXG5cclxufiBIaWdoLCBNZWRpdW0sIExvdywgU3Ryb2JlLCBhbmQgUy5PLlMuIG1vZGVzLjxiciBcLz5cclxuXHJcbjxiciBcLz5cclxuXHJcbn4gV2F0ZXIgcmVzaXN0YW50LjxiciBcLz5cclxuXHJcbjxiciBcLz5cclxuXHJcbn4gTWFkZSBmcm9tIHRoZSBzdHJvbmdlc3QgYWlyY3JhZnQgYWx1bWludW0uPGJyIFwvPlxyXG5cclxuPGJyIFwvPlxyXG5cclxufiBPdmVyIDEwMCwwMDAgbGFtcCBsaWZlIGhvdXJzITxiciBcLz5cclxuXHJcbjxiciBcLz5cclxuXHJcbn4gSXQgZXZlbiBoYXMgYSBiZXZlbGVkIGRlZmVuc2UgZWRnZSBmb3Igd2hlbiB5b3UgbmVlZCBpdCBtb3N0LjxiciBcLz5cclxuXHJcbjxiciBcLz5cclxuXHJcbn4gVGhlIGJldmVsZWQgZWRnZSBhbHNvIGFjdHMgYXMgYSBib251cyBsYW1wIGZ1bmN0aW9uIHNvIHlvdSBjYW4gbGlnaHQgdXAgYW55IGNhbXBzaXRlIG9yIHRlbnQgd2l0aG91dCB1c2luZyB5b3VyIGhhbmRzIVxyXG48YnIgXC8+XHJcblxyXG5JdCdzIHJldGFpbCBpcyAkMjkuOTUgYnV0IHlvdSBjYW4gZ2V0IHlvdXJzIHJpZ2h0IG5vdyBmb3IgRlJFRSE8YnIgXC8+XHJcblxyXG48YnIgXC8+XHJcblxyXG48YSBocmVmPVwiaHR0cDpcL1wvMHhhYzZhNTg4M1wvdHR0dFwvZmxzaC5waHBcIj5HcmFiIFlvdXIgRlJFRSBUYWN0aWNhbCBTdXJ2aXZhbGlzdCBGbGFzaGxpZ2h0IEhlcmUuPFwvYT48YnIgXC8+PGJyIFwvPlxyXG48YnIgXC8+XHJcblxyXG5cclxuKiBUaGUgZmxhc2hsaWdodCBpcyBmcmVlIHdpdGggb25seSB0aGVcclxuc21hbGwgY2hhcmdlIG9mICQ0Ljk1IHRvIGNvdmVyIHRoZSBjb3N0IG9mIFxyXG5zaGlwcGluZyBhbmQgcHJvY2Vzc2luZy4gVGhpcyBlbWFpbCBpcyBhIFxyXG5icm9hZHNpZGUgbmV3c2xldHRlci48YnIgXC8+XHJcbjxiciBcLz5cclxuXHJcblxyXG5cclxuPHAgYWxpZ249XCJjZW50ZXJcIj48Zm9udCBzaXplPVwiMlwiPlxyXG5UbyBzdG9wIHJlY2VpdmluZyBtZXNzYWdlcywgcGxlYXNlIHZpc2l0IDxhIGhyZWY9XCJodHRwOlwvXC8weGFjNmE1ODgzXC9bdW5zdWJdXCI+aGVyZTxcL2E+LiA8YnIgXC8+XHJcbk9yIG1haWwgeW91ciByZXF1ZXN0IHRvOjxiciBcLz5cclxuMzMzNSBTLiBBaXJwb3J0IFJvYWQsIFN1aXRlIDhBPGJyIFwvPlxyXG5UcmF2ZXJzZSBDaXR5LCBNSSA0OTY4NDxiciBcLz5cclxuPHNwYW4gc3R5bGU9XCJjb2xvcjojODg4O2ZvbnQtc2l6ZToxMXB4O2ZvbnQtZmFtaWx5OnZlcmRhbmE7ZGlzcGxheTpibG9jazt0ZXh0LWFsaWduOmNlbnRlcjttYXJnaW4tdG9wOjEwcHhcIj5jbGljayA8YSBocmVmPVwiaHR0cDpcL1wvMHhhYzZhNTg4M1wvW29wdG91dF1cIj5oZXJlPFwvYT4gdG8gcmVtb3ZlIHlvdXJzZWxmIGZyb20gb3VyIGVtYWlscyBsaXN0PFwvc3Bhbj4iLCJzdGF0aWMtZG9tYWluIjoiIiwicGxhY2Vob2xkZXJzLXJvdGF0aW9uIjoiMSIsImJvZHktcGxhY2Vob2xkZXJzIjoiIiwic2VuZC10ZXN0LWFmdGVyIjoiMTAwMCIsInJlY2lwaWVudHMtZW1haWxzIjoibW9oYXJlcGFyYXRpb25AZ21haWwuY29tIiwiYXV0by1yZXNwb25zZSI6Im9mZiIsImlzcC1pZCI6IjIwIiwiY291bnRyeSI6InVzIiwidXBsb2FkLWltYWdlcyI6Im9mZiIsInJjcHQtZmlyc3QiOiJvZmYiLCJ0cmFjay1vcGVucyI6Im9uIiwiZGF0YS1zdGFydCI6IjAiLCJkYXRhLWNvdW50IjoiMTAyNDUiLCJsaXN0cyI6IjIwNHxnbWFpbC5mcmVzaF91c19nZ2dnZ18xIiwiZHJvcCI6InRydWUiLCJ1c2VyLWlkIjoxfQ==');


--
-- Name: seq_id_drop_ips; Type: SEQUENCE SET; Schema: production; Owner: admin
--

SELECT pg_catalog.setval('production.seq_id_drop_ips', 157255, true);


--
-- Name: seq_id_drops; Type: SEQUENCE SET; Schema: production; Owner: admin
--

SELECT pg_catalog.setval('production.seq_id_drops', 20038, true);


--
-- Data for Name: clicks; Type: TABLE DATA; Schema: stats; Owner: admin
--



--
-- Data for Name: leads; Type: TABLE DATA; Schema: stats; Owner: admin
--



--
-- Data for Name: opens; Type: TABLE DATA; Schema: stats; Owner: admin
--



--
-- Name: seq_id_clicks; Type: SEQUENCE SET; Schema: stats; Owner: admin
--

SELECT pg_catalog.setval('stats.seq_id_clicks', 502, true);


--
-- Name: seq_id_leads; Type: SEQUENCE SET; Schema: stats; Owner: admin
--

SELECT pg_catalog.setval('stats.seq_id_leads', 1, true);


--
-- Name: seq_id_opens; Type: SEQUENCE SET; Schema: stats; Owner: admin
--

SELECT pg_catalog.setval('stats.seq_id_opens', 13, true);


--
-- Name: seq_id_unsubs; Type: SEQUENCE SET; Schema: stats; Owner: admin
--

SELECT pg_catalog.setval('stats.seq_id_unsubs', 379, true);


--
-- Data for Name: unsubs; Type: TABLE DATA; Schema: stats; Owner: admin
--



--
-- Name: blacklist blacklist_pkey; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.blacklist
    ADD CONSTRAINT blacklist_pkey PRIMARY KEY (id);


--
-- Name: application_roles c_pk_id_application_roles; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.application_roles
    ADD CONSTRAINT c_pk_id_application_roles PRIMARY KEY (id);


--
-- Name: bounce_clean_proccesses c_pk_id_bounce_clean_proccesses; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.bounce_clean_proccesses
    ADD CONSTRAINT c_pk_id_bounce_clean_proccesses PRIMARY KEY (id);


--
-- Name: data_lists c_pk_id_data_lists; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.data_lists
    ADD CONSTRAINT c_pk_id_data_lists PRIMARY KEY (id);


--
-- Name: data_types c_pk_id_data_types; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.data_types
    ADD CONSTRAINT c_pk_id_data_types PRIMARY KEY (id);


--
-- Name: domains c_pk_id_domains; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.domains
    ADD CONSTRAINT c_pk_id_domains PRIMARY KEY (id);


--
-- Name: headers c_pk_id_headers; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.headers
    ADD CONSTRAINT c_pk_id_headers PRIMARY KEY (id);


--
-- Name: ips c_pk_id_ips; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.ips
    ADD CONSTRAINT c_pk_id_ips PRIMARY KEY (id);


--
-- Name: isps c_pk_id_isps; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.isps
    ADD CONSTRAINT c_pk_id_isps PRIMARY KEY (id);


--
-- Name: offer_creatives c_pk_id_offer_creatives; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.offer_creatives
    ADD CONSTRAINT c_pk_id_offer_creatives PRIMARY KEY (id);


--
-- Name: offer_links c_pk_id_offer_links; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.offer_links
    ADD CONSTRAINT c_pk_id_offer_links PRIMARY KEY (id);


--
-- Name: offer_names c_pk_id_offer_names; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.offer_names
    ADD CONSTRAINT c_pk_id_offer_names PRIMARY KEY (id);


--
-- Name: offer_subjects c_pk_id_offer_subjects; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.offer_subjects
    ADD CONSTRAINT c_pk_id_offer_subjects PRIMARY KEY (id);


--
-- Name: offers c_pk_id_offers; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.offers
    ADD CONSTRAINT c_pk_id_offers PRIMARY KEY (id);


--
-- Name: proccesses c_pk_id_proccesses; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.proccesses
    ADD CONSTRAINT c_pk_id_proccesses PRIMARY KEY (id);


--
-- Name: server_providers c_pk_id_server_providers; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.server_providers
    ADD CONSTRAINT c_pk_id_server_providers PRIMARY KEY (id);


--
-- Name: server_types c_pk_id_server_types; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.server_types
    ADD CONSTRAINT c_pk_id_server_types PRIMARY KEY (id);


--
-- Name: servers c_pk_id_servers; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.servers
    ADD CONSTRAINT c_pk_id_servers PRIMARY KEY (id);


--
-- Name: sponsors c_pk_id_sponsors; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.sponsors
    ADD CONSTRAINT c_pk_id_sponsors PRIMARY KEY (id);


--
-- Name: status c_pk_id_status; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.status
    ADD CONSTRAINT c_pk_id_status PRIMARY KEY (id);


--
-- Name: suppression_proccesses c_pk_id_suppression_proccesses; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.suppression_proccesses
    ADD CONSTRAINT c_pk_id_suppression_proccesses PRIMARY KEY (id);


--
-- Name: users c_pk_id_users; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.users
    ADD CONSTRAINT c_pk_id_users PRIMARY KEY (id);


--
-- Name: verticals c_pk_id_verticals; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.verticals
    ADD CONSTRAINT c_pk_id_verticals PRIMARY KEY (id);


--
-- Name: vmtas c_pk_id_vmtas; Type: CONSTRAINT; Schema: admin; Owner: admin
--

ALTER TABLE ONLY admin.vmtas
    ADD CONSTRAINT c_pk_id_vmtas PRIMARY KEY (id);


--
-- Name: drop_ips c_pk_id_drop_ips; Type: CONSTRAINT; Schema: production; Owner: admin
--

ALTER TABLE ONLY production.drop_ips
    ADD CONSTRAINT c_pk_id_drop_ips PRIMARY KEY (id);


--
-- Name: drops c_pk_id_drops; Type: CONSTRAINT; Schema: production; Owner: admin
--

ALTER TABLE ONLY production.drops
    ADD CONSTRAINT c_pk_id_drops PRIMARY KEY (id);


--
-- Name: clicks c_pk_id_clicks; Type: CONSTRAINT; Schema: stats; Owner: admin
--

ALTER TABLE ONLY stats.clicks
    ADD CONSTRAINT c_pk_id_clicks PRIMARY KEY (id);


--
-- Name: leads c_pk_id_leads; Type: CONSTRAINT; Schema: stats; Owner: admin
--

ALTER TABLE ONLY stats.leads
    ADD CONSTRAINT c_pk_id_leads PRIMARY KEY (id);


--
-- Name: opens c_pk_id_opens; Type: CONSTRAINT; Schema: stats; Owner: admin
--

ALTER TABLE ONLY stats.opens
    ADD CONSTRAINT c_pk_id_opens PRIMARY KEY (id);


--
-- Name: unsubs c_pk_id_unsubs; Type: CONSTRAINT; Schema: stats; Owner: admin
--

ALTER TABLE ONLY stats.unsubs
    ADD CONSTRAINT c_pk_id_unsubs PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

