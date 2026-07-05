CREATE TABLE article
(
    line_num   INTEGER INTEGER PRIMARY KEY,
    book_id    TEXT, /* 書籍ID */
    id_me      INTEGER, /* 書籍ID */
    id_parent  INTEGER, /* 書籍ID */
    type       TEXT, /* 区分（章・節・条・項など） */
    num        TEXT, /* セクション番号（例: 1, 2-1） */
    num_parent TEXT, /* 親セクションの番号 */
    depth      INTEGER, /* 階層の深さ */
    title      TEXT, /* タイトル */
    content    TEXT /* 本文 */
);