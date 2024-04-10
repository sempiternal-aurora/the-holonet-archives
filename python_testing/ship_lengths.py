import mariadb
import html

mydb = mariadb.connect(
    host = 'localhost',
    user = 'php_console',
    password = 'mysql',
    database = 'holonet'
)

mycursor = mydb.cursor()

mycursor.execute("UPDATE unit SET width=0 WHERE width IS NULL AND unit_type=1")
mycursor.execute("UPDATE unit SET length=0 WHERE length IS NULL AND unit_type=1")
mycursor.execute("UPDATE unit SET height=0 WHERE height IS NULL AND unit_type=1")

mycursor.execute("SELECT name, length, height, width FROM unit WHERE unit_type=1 ORDER BY GREATEST(length, height, width) ASC, name ASC")

with open('ship_lengths.txt', 'w+') as f:
    for x in mycursor:
        dimensions_string = x[0] + " | "
        if x[1] != 0:
            dimensions_string += " Length: " + str(x[1]) + "m"
        if x[2] != 0:
            dimensions_string += " Height: " + str(x[2]) + "m"
        if x[3] != 0:
            dimensions_string += " Width: " + str(x[3]) + "m"
        dimensions_string = html.unescape(dimensions_string)
        f.write(dimensions_string + "\n")

mycursor.execute("UPDATE unit SET width=NULL WHERE width=0 AND unit_type=1")
mycursor.execute("UPDATE unit SET length=NULL WHERE length=0 AND unit_type=1")
mycursor.execute("UPDATE unit SET height=NULL WHERE height=0 AND unit_type=1")

mycursor.close()