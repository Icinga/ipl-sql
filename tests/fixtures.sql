CREATE TABLE user (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  username VARCHAR NOT NULL,
  password VARCHAR NOT NULL,
  ctime TIMESTAMP NOT NULL,
  mtime TIMESTAMP NOT NULL,
  UNIQUE(username)
);

INSERT INTO user (id, username, password, ctime, mtime) VALUES
(1, 'admin', 'admin', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'guest', 'guest', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);