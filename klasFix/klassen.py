with open("klassen.txt", "r") as f:
    klassen = f.read()
arr = klassen.split("\n")

klassen = []

for klas in arr:
    if (len(klas) < 3):
        continue
    k = klas.split("\t")
    kString = k[0]+k[1]+k[2]
    kJaar = k[0]
    kObj = {"jaar":kJaar, "naam": kString}
    klassen.append(kObj)


SQL = "INSERT INTO klassen (jaar, klasNaam) VALUES (\"%s\", \"%s\");\n"

SQLSTRING = ""


for klas in klassen:
    SQLSTRING += SQL % (klas['jaar'], klas['naam'])

print(SQLSTRING)
