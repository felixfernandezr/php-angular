BEGIN TRANSACTION;
DROP TABLE IF EXISTS "peliculas_generos";
CREATE TABLE IF NOT EXISTS "peliculas_generos" (
	"id_pelicula"	INTEGER,
	"id_genero"	INTEGER
);
DROP TABLE IF EXISTS "generos";
CREATE TABLE IF NOT EXISTS "generos" (
	"id"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"descripcion"	TEXT
);
DROP TABLE IF EXISTS "peliculas";
CREATE TABLE IF NOT EXISTS "peliculas" (
	"id"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"titulo"	TEXT,
	"anio"	INTEGER
);
INSERT INTO "peliculas_generos" ("id_pelicula","id_genero") VALUES (1,3);
INSERT INTO "peliculas_generos" ("id_pelicula","id_genero") VALUES (1,1);
INSERT INTO "peliculas_generos" ("id_pelicula","id_genero") VALUES (2,2);
INSERT INTO "generos" ("id","descripcion") VALUES (1,'Acción');
INSERT INTO "generos" ("id","descripcion") VALUES (2,'Comedia');
INSERT INTO "generos" ("id","descripcion") VALUES (3,'Drama');
INSERT INTO "peliculas" ("id","titulo","anio") VALUES (1,'El padrino',1973);
INSERT INTO "peliculas" ("id","titulo","anio") VALUES (2,'Volver al futuro',1985);
INSERT INTO "peliculas" ("id","titulo","anio") VALUES (3,'Star wars: una nueva esperanza',1977);
COMMIT;
