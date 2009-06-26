Changements principaux:
- la classe AbstractObject est sortie du biz model
- join_type remplac� par is_null_allowed (plac� � la fin pour �tre + facile � retrouver)
- j'ai enlev� toute la classe logLocatedObject qui �tait en commentaire
- Enlev� 'address' de l'advanced search sur une location car ce n'est plus un crit�re de recherche possible (remplac� par country)
- Ajout� des crit�res de recherche sur bizCircuit
- Ajout� les ZList sur bizCircuit
- Ajout� les Zlist pour bizInterface
- Ajout� les Zlist pour lnkInfraInfra
- Ajout� les Zlist pour lnkInfraTicket

Dans AbstractObject: d�sactiv� l'affichage des contacts li�s qui ne marche pas pour les tickets.

Bug fix ?
- J'ai rajout� un blindage if (is_object($proposedValue) &&... dans AttributeDate::MakeRealValue mais je ne comprends pas d'o� sort la classe DateTime... et pourtant il y en a...

Am�liorations:
- Ajouter une v�rification des ZList (les attributs/crit�resde recherche d�clar�s dans la liste existent-ils pour cet objet)

Ne marche pas:
- Objets avec des clefs externes vides
- Enums !!!!

Data Generator:
Organization '1' updated.
5 Location objects created.
19 PC objects created.
19 Network Device objects created.
42 Person objects created.
6 Incident objects created.
17 Infra Group objects created.
34 Infra Infra objects created.
