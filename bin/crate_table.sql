DROP TABLE IF EXISTS article;
CREATE TABLE article
(
    id_book     TEXT,
    id_me       INTEGER,
    id_parent   INTEGER,
    id_depth    INTEGER,
    num         TEXT,
    name        TEXT,
    title       TEXT,
    caption     TEXT,
    article_xml TEXT
);