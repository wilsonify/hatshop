-- Create catalog_create_product_review function
CREATE FUNCTION catalog_create_product_review(INTEGER, INTEGER, TEXT,
                                              SMALLINT)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId ALIAS FOR $1;
    inProductId  ALIAS FOR $2;
    inReview     ALIAS FOR $3;
    inRating     ALIAS FOR $4;
  BEGIN
    INSERT INTO review (customer_id, product_id, review, rating, created_on)
           VALUES (inCustomerId, inProductId, inReview, inRating, NOW());
  END;
$$;

--------------------------------------------------------------------------------

-- Populate tables

-- Populate department table
INSERT INTO department (department_id, name, description)
       VALUES(1, 'Holiday', 'Prepare for the holidays with our special collection of seasonal hats!');
INSERT INTO department (department_id, name, description)
       VALUES(2, 'Caps and Berets', 'The perfect hats to wear at work and costume parties!');
INSERT INTO department (department_id, name, description)
       VALUES(3, 'Costume Hats', 'Find the matching hat for your new costume!');

-- Update the sequence
ALTER SEQUENCE department_department_id_seq RESTART WITH 4;

-- Populate category table
INSERT INTO category (category_id, department_id, name, description)
       VALUES (1, 1, 'Christmas Hats', 'Enjoy browsing our collection of Christmas hats!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (2, 1, 'Halloween Hats', 'Find the hat you''ll wear this Halloween!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (3, 1, 'St. Patrick''s Day Hats', 'Try one of these beautiful hats on St. Patrick''s Day!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (4, 2, 'Berets', 'An amazing collection of berets from all around the world!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (5, 2, 'Driving Caps', 'Be an original driver! Buy a driver''s hat today!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (6, 3, 'Theatrical Hats', 'Going to a costume party? Try one of these hats to complete your costume!');
INSERT INTO category (category_id, department_id, name, description)
       VALUES (7, 3, 'Military Hats', 'This collection contains the most realistic replicas of military hats!');

-- Update the sequence
ALTER SEQUENCE category_category_id_seq RESTART WITH 8;

-- Populate product table
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (1, 'Christmas Candy Hat', 'Be everyone''s "sweetie" while wearing this fun and festive peppermint candy hat. The Christmas Candy hat, made by Elope, stands about 15 inches tall and has a sizing adjustment on the inside.', '24.99', '0.00', 'ChristmasCandyHat.jpg', 'ChristmasCandyHat.thumb.jpg', 0, '''15'':26 ''fun'':11 ''hat'':3A,16,20 ''elop'':23 ''inch'':27 ''made'':21 ''size'':32 ''tall'':28 ''wear'':9 ''candi'':2A,15,19 ''insid'':36 ''stand'':24 ''adjust'':33 ''festiv'':13 ''sweeti'':7 ''everyon'':5 ''christma'':1A,18 ''peppermint'':14');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (2, 'Hanukah Hat', 'The Hanukah hat is a fun and festive way for you to enjoy yourself during the holiday. Made by Elope and adorned with 9 candles, this menorah is sure to brighten the winter celebration.', '24.99', '21.99', 'HanukahHat.jpg', 'HanukahHat.thumb.jpg', 2, '''9'':26 ''fun'':8 ''hat'':2A,5 ''way'':11 ''elop'':22 ''made'':20 ''sure'':31 ''adorn'':24 ''candl'':27 ''enjoy'':15 ''celebr'':36 ''festiv'':10 ''winter'':35 ''hanukah'':1A,4 ''holiday'':19 ''menorah'':29 ''brighten'':33');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (3, 'Springy Santa Hat', 'Santa Hat - Boing-Boing-Boing. Santa will be springing into town with this outrageous cap! If your children are whiney and clingy ... and your head''s going ping-pong-pingy ... and you feel like just rowing away in your rubber dinghy ... Take heart! You''ll bounce bounce back ... if you just put on our Santa hat that''s Springy!', '19.99', '0.00', 'SpringySantaHat.jpg', 'SpringySantaHat.thumb.jpg', 0, '''bo'':7,8,9 ''go'':31 ''ll'':50 ''cap'':19 ''hat'':3A,5,61 ''put'':57 ''row'':41 ''away'':42 ''back'':53 ''feel'':38 ''head'':29 ''like'':39 ''ping'':33 ''pong'':34 ''take'':47 ''town'':15 ''bounc'':51,52 ''heart'':48 ''pingi'':35 ''santa'':2A,4,10,60 ''clingi'':26 ''dinghi'':46 ''outrag'':18 ''rubber'':45 ''spring'':13 ''whiney'':24 ''springi'':1A,64 ''children'':22 ''boing-boing-bo'':6 ''ping-pong-pingi'':32');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (4, 'Plush Santa Hat', 'Get into the spirit of the season with this plush, velvet-like, Santa hat. Comes in a beautiful crimson red color with a faux-fur trim.', '12.99', '0.00', 'PlushSantaHat.jpg', 'PlushSantaHat.thumb.jpg', 0, '''fur'':30 ''get'':4 ''hat'':3A,18 ''red'':24 ''come'':19 ''faux'':29 ''like'':16 ''trim'':31 ''color'':25 ''plush'':1A,13 ''santa'':2A,17 ''beauti'':22 ''season'':10 ''spirit'':7 ''velvet'':15 ''crimson'':23 ''faux-fur'':28 ''velvet-lik'':14');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (5, 'Red Santa Cowboy Hat', 'This velvet Cowboy Santa Hat is one size fits all and has white faux-fur trim all around. Here comes Santa Claus ... Here comes Santa Claus ... right down Cowboy Lane!', '24.99', '0.00', 'RedSantaCowboyHat.jpg', 'RedSantaCowboyHat.thumb.jpg', 0, '''fit'':13 ''fur'':20 ''hat'':4A,9 ''one'':11 ''red'':1A ''come'':25,29 ''faux'':19 ''lane'':35 ''size'':12 ''trim'':21 ''claus'':27,31 ''right'':32 ''santa'':2A,8,26,30 ''white'':17 ''around'':23 ''cowboy'':3A,7,34 ''velvet'':6 ''faux-fur'':18');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (6, 'Santa Jester Hat', 'This three-prong velvet jester is one size fits all and has an adjustable touch fastener back for perfect fitting.', '24.99', '0.00', 'SantaJesterHat.jpg', 'SantaJesterHat.thumb.jpg', 0, '''fit'':13,24 ''hat'':3A ''one'':11 ''back'':21 ''size'':12 ''prong'':7 ''santa'':1A ''three'':6 ''touch'':19 ''adjust'':18 ''fasten'':20 ''jester'':2A,9 ''velvet'':8 ''perfect'':23 ''three-prong'':5');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (7, 'Santa''s Elf Hat', 'Be Santa''s best looking helper with this velvet hat, complete with attached ears. So, if you don''t wanna be yourself ... don''t worry ... this Christmas, just be Santa''s elf! Happy Holidays!', '24.99', '16.95', 'Santa''sElfHat.jpg', 'Santa''sElfHat.thumb.jpg', 1, '''ear'':18 ''elf'':3A,36 ''hat'':4A,14 ''best'':8 ''look'':9 ''happi'':37 ''santa'':1A,6,34 ''wanna'':24 ''worri'':29 ''attach'':17 ''helper'':10 ''velvet'':13 ''complet'':15 ''holiday'':38 ''christma'':31');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (8, 'Chauffeur Hat', 'Uniform Chauffeur Cap. This cap is the real thing. Well-made and professional looking, our Chauffeur hat gets so many compliments from our customers who buy (and wear) them. It''s absolutely amazing how many of these we sell. One thing''s for sure, this authentic professional cap will let everyone know exactly who''s in the driver''s seat. So ... whether you''re driving Miss Daisy ... or driving yourself crazy ... I''ll bet your wife will coo and purr ... when she sees you in our authentic chauffer!', '69.99', '0.00', 'ChauffeurHat.jpg', 'ChauffeurHat.thumb.jpg', 0, '''ll'':76 ''re'':67 ''bet'':77 ''buy'':29 ''cap'':5,7,51 ''coo'':81 ''get'':21 ''hat'':2A,20 ''let'':53 ''one'':43 ''see'':86 ''amaz'':36 ''know'':55 ''look'':17 ''made'':14 ''mani'':23,38 ''miss'':69 ''purr'':83 ''real'':10 ''seat'':63 ''sell'':42 ''sure'':47 ''wear'':31 ''well'':13 ''wife'':79 ''crazi'':74 ''daisi'':70 ''drive'':68,72 ''exact'':56 ''thing'':11,44 ''custom'':27 ''driver'':61 ''absolut'':35 ''authent'':49,90 ''everyon'':54 ''uniform'':3 ''whether'':65 ''chauffer'':91 ''well-mad'':12 ''chauffeur'':1A,4,19 ''compliment'':24 ''profession'':16,50');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (9, 'The Pope Hat', 'We''re not sure what the Vatican''s official position is on this hat, but we do know your friends will love you in this soft velour hat with gold lame accents. If you''re somewhat lacking in religion ... if you''re looking for some hope ... you might acquire just a smidgeon ... by faithfully wearing our Pope!', '29.99', '0.00', 'ThePopeHat.jpg', 'ThePopeHat.thumb.jpg', 0, '''re'':5,38,45 ''hat'':3A,17,31 ''gold'':33 ''hope'':49 ''know'':21 ''lack'':40 ''lame'':34 ''look'':46 ''love'':25 ''pope'':2A,60 ''soft'':29 ''sure'':7 ''wear'':58 ''faith'':57 ''might'':51 ''posit'':13 ''accent'':35 ''acquir'':52 ''friend'':23 ''offici'':12 ''velour'':30 ''vatican'':10 ''religion'':42 ''smidgeon'':55 ''somewhat'':39');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (10, 'Vinyl Policeman Cop Hat', 'A hat that channels the 70s. This oversized vinyl cap with silver badge will make you a charter member of the disco era ... or is that disco error?', '29.99', '0.00', 'VinylPolicemanCopHat.jpg', 'VinylPolicemanCopHat.thumb.jpg', 0, '''70s'':10 ''cap'':14 ''cop'':3A ''era'':27 ''hat'':4A,6 ''badg'':17 ''make'':19 ''disco'':26,31 ''error'':32 ''overs'':12 ''vinyl'':1A,13 ''member'':23 ''silver'':16 ''channel'':8 ''charter'':22 ''policeman'':2A');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (11, 'Burgundy Kings Crown', 'Our burgundy Kings Crown is one size fits all. This crown is adorned with gold ribbon, gems, and a faux-fur headband. Be King for a Day ... Finally get your say ... Put your foot down ... and do it with humor, wearing our Elegant Burgundy King''s Crown!', '34.99', '25.95', 'BurgandyKingsCrown.jpg', 'BurgandyKingsCrown.thumb.jpg', 2, '''day'':31 ''fit'':11 ''fur'':25 ''gem'':20 ''get'':33 ''one'':9 ''put'':36 ''say'':35 ''eleg'':47 ''faux'':24 ''foot'':38 ''gold'':18 ''king'':2A,6,28,49 ''size'':10 ''wear'':45 ''adorn'':16 ''crown'':3A,7,14,51 ''final'':32 ''humor'':44 ''ribbon'':19 ''burgundi'':1A,5,48 ''faux-fur'':23 ''headband'':26');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (12, '454 Black Pirate Hat', 'Our wool felt Pirate Hat comes with the front and back brims turned upward. This sized hat has the pirate emblem on the front. So, ho, ho, ho and a bottle of rum ... if you''re about as crazy as they come ... wear our Pirate hat this coming Halloween ... and with an eyepatch to boot, you''ll be lusty, lean and mean!', '39.99', '0.00', '454BlackPirateHat.jpg', '454BlackPirateHat.thumb.jpg', 0, '''ho'':30,31,32 ''ll'':61 ''re'':40 ''454'':1A ''hat'':4A,9,21,50 ''rum'':37 ''back'':15 ''boot'':59 ''brim'':16 ''come'':10,46,52 ''felt'':7 ''lean'':64 ''mean'':66 ''size'':20 ''turn'':17 ''wear'':47 ''wool'':6 ''black'':2A ''bottl'':35 ''crazi'':43 ''front'':13,28 ''lusti'':63 ''pirat'':3A,8,24,49 ''emblem'':25 ''upward'':18 ''eyepatch'':57 ''halloween'':53');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (13, 'Black Puritan Hat', 'Haentze Hatcrafters has been making quality theatrical and military headgear for decades. Each hat is made meticulously by hand with quality materials. Many of these hats have been used in movies and historical reproductions and re-enactments.', '89.99', '75.99', 'BlackPuritanHat.jpg', 'BlackPuritanHat.thumb.jpg', 2, '''re'':40 ''hat'':3A,17,29 ''use'':32 ''hand'':22 ''made'':19 ''make'':8 ''mani'':26 ''movi'':34 ''black'':1A ''decad'':15 ''enact'':41 ''haentz'':4 ''histor'':36 ''materi'':25 ''meticul'':20 ''puritan'':2A ''qualiti'':9,24 ''hatcraft'':5 ''headgear'':13 ''militari'':12 ''re-enact'':39 ''theatric'':10 ''reproduct'':37');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (14, 'Professor McGonagall Witch Hat', 'Professor McGonagall, Deputy Headmistress of Hogwarts and Head of Gryffindor House, commands respect in this dramatic witch hat - and so will you! The inside of this hat has a touch fastener size-adjustment tab. The hat is a must for all Harry Potter fans!', '24.99', '0.00', 'ProfessorMcGonagallWitchHat.jpg', 'ProfessorMcGonagallWitchHat.thumb.jpg', 0, '''fan'':49 ''hat'':4A,22,31,41 ''tab'':39 ''head'':12 ''hous'':15 ''must'':44 ''size'':37 ''harri'':47 ''insid'':28 ''touch'':34 ''witch'':3A,21 ''adjust'':38 ''deputi'':7 ''dramat'':20 ''fasten'':35 ''potter'':48 ''command'':16 ''hogwart'':10 ''respect'':17 ''mcgonagal'':2A,6 ''professor'':1A,5 ''gryffindor'':14 ''size-adjust'':36 ''headmistress'':8');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (15, 'Black Wizard Hat', 'This cool Merlin-style wizard hat by Elope has a dragon that lays around the whole hat. The wizard hat is one size fits all and has a touch fastener on the inside to adjust accordingly.', '34.99', '0.00', 'BlackWizardHat.jpg', 'BlackWizardHat.thumb.jpg', 0, '''fit'':28 ''hat'':3A,10,21,24 ''lay'':17 ''one'':26 ''cool'':5 ''elop'':12 ''size'':27 ''black'':1A ''insid'':37 ''style'':8 ''touch'':33 ''whole'':20 ''accord'':40 ''adjust'':39 ''around'':18 ''dragon'':15 ''fasten'':34 ''merlin'':7 ''wizard'':2A,9,23 ''merlin-styl'':6');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (16, 'Leprechaun Hat', 'Show them the green! This hand-blocked, wool felt hat will make you the hit of this year''s St. Paddy''s Day Celebration! Oh yes, the green you will don ... and what better way, hon ... than if this St. Patrick''s Day ... you''re wearing our cool Leprechaun!', '88.99', '0.00', 'LeprechaunHat.jpg', 'LeprechaunHat.thumb.jpg', 0, '''oh'':28 ''re'':48 ''st'':23,43 ''day'':26,46 ''hat'':2A,13 ''hit'':18 ''hon'':39 ''way'':38 ''yes'':29 ''cool'':51 ''felt'':12 ''hand'':9 ''make'':15 ''show'':3 ''wear'':49 ''wool'':11 ''year'':21 ''block'':10 ''green'':6,31 ''paddi'':24 ''better'':37 ''celebr'':27 ''patrick'':44 ''hand-block'':8 ''leprechaun'':1A,52');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (17, '9 Green MadHatter Top Hat', 'Each of our MadHatter hats is made meticulously by hand with quality materials. Many of these hats have been used in movies and historical reproductions and re-enactments.', '149.99', '124.95', '9GreenMadHatterTopHat.jpg', '9GreenMadHatterTopHat.thumb.jpg', 2, '''9'':1A ''re'':33 ''hat'':5A,10,22 ''top'':4A ''use'':25 ''hand'':15 ''made'':12 ''mani'':19 ''movi'':27 ''enact'':34 ''green'':2A ''histor'':29 ''materi'':18 ''madhatt'':3A,9 ''meticul'':13 ''qualiti'':17 ''re-enact'':32 ''reproduct'':30');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (18, 'Winter Walking Hat', 'Our declarative English walking hat by Christy''s of London comes in 100% Lana Wool and reveals a finished satin lining. Christy''s has been making hats since 1773 and knows how to make the best! Want proof? Try this one ... Irish eyes will be smiling.', '49.99', '0.00', 'WinterWalkingHat.jpg', 'WinterWalkingHat.thumb.jpg', 0, '''100'':16 ''eye'':46 ''hat'':3A,8,30 ''one'':44 ''tri'':42 ''1773'':32 ''best'':39 ''come'':14 ''know'':34 ''lana'':17 ''line'':24 ''make'':29,37 ''sinc'':31 ''walk'':2A,7 ''want'':40 ''wool'':18 ''irish'':45 ''proof'':41 ''satin'':23 ''smile'':49 ''declar'':5 ''finish'':22 ''london'':13 ''reveal'':20 ''winter'':1A ''christi'':10,25 ''english'':6');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (19, 'Green MadHatter Hat', 'St. Patrick''s Day Hat - Luck o'' the Irish! This oversized green velveteen MadHatter is great for St.Patrick''s day or a MadHatter''s tea party.One size fits most adults.', '39.99', '28.99', 'GreenMadHatterHat.jpg', 'GreenMadHatterHat.thumb.jpg', 2, '''o'':10 ''st'':4 ''day'':7,23 ''fit'':31 ''hat'':3A,8 ''tea'':28 ''luck'':9 ''size'':30 ''adult'':33 ''great'':19 ''green'':1A,15 ''irish'':12 ''overs'':14 ''madhatt'':2A,17,26 ''patrick'':5 ''party.one'':29 ''velveteen'':16 ''st.patrick'':21');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (20, 'Hole in One Golf Costume Hat', 'Golf Hat - OK, Ace. This spoof golfer''s hat sports an astro-turf "green," has an attached golf ball and flag, and includes a soft elastic band  for comfort. This hat also makes a great gift that is definitely "up to par" for any goofball''s - uh - golfballer''s taste. Perfect for Dad! And don''t you fore-get-it!', '29.99', '0.00', 'HoleinOneGolfCostumeHat.jpg', 'HoleinOneGolfCostumeHat.thumb.jpg', 0, '''ok'':9 ''uh'':54 ''ace'':10 ''dad'':60 ''get'':67 ''hat'':6A,8,15,38 ''one'':3A ''par'':49 ''also'':39 ''ball'':26 ''band'':34 ''flag'':28 ''fore'':66 ''gift'':43 ''golf'':4A,7,25 ''hole'':1A ''make'':40 ''soft'':32 ''tast'':57 ''turf'':20 ''astro'':19 ''elast'':33 ''great'':42 ''green'':21 ''spoof'':12 ''sport'':16 ''attach'':24 ''costum'':5A ''golfer'':13 ''includ'':30 ''comfort'':36 ''definit'':46 ''golfbal'':55 ''goofbal'':52 ''perfect'':58 ''astro-turf'':18 ''fore-get-it'':65');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (21, 'Luck of the Irish Bowler', 'This one size fits all Irish Derby comes with a shamrock attached to the side. This hat is made of a soft velvet and is comfortably sized.', '29.99', '0.00', 'LuckoftheIrishBowler.jpg', 'LuckoftheIrishBowler.thumb.jpg', 0, '''fit'':9 ''hat'':22 ''one'':7 ''come'':13 ''luck'':1A ''made'':24 ''side'':20 ''size'':8,32 ''soft'':27 ''derbi'':12 ''irish'':4A,11 ''attach'':17 ''bowler'':5A ''velvet'':28 ''comfort'':31 ''shamrock'':16');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (22, 'St. Patrick''s Irish Green Derby', 'This quality bowler will last you more than one St. Patrick''s Day! A proper derby for the day, it is made of wool felt and has a green grosgrain band. This hat is not lined.', '39.99', '0.00', 'St.Patrick''sIrishGreenDerby.jpg', 'St.Patrick''sIrishGreenDerby.thumb.jpg', 0, '''st'':1A,16 ''day'':19,25 ''hat'':39 ''one'':15 ''band'':37 ''felt'':31 ''last'':11 ''line'':42 ''made'':28 ''wool'':30 ''derbi'':6A,22 ''green'':5A,35 ''irish'':4A ''bowler'':9 ''proper'':21 ''patrick'':2A,17 ''qualiti'':8 ''grosgrain'':36');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (23, 'Black Basque Beret', 'This is our tried and true men''s classic beret hat(tam). Our Basque beret is superbly crafted, 100% wool, and has a comfortable leather sweatband. Lined and water resistant, the beret is great for everyday wear and rolls up nicely to fit in your pocket. So ... if you''re antsy ... in your pantsy ... cause you wanna catch the fancy ... of the lady near your way ... then please don''t delay ... just get this beret ... today ... and soon you''ll be making hay!', '49.99', '0.00', 'BlackBasqueBeret.jpg', 'BlackBasqueBeret.thumb.jpg', 0, '''ll'':83 ''re'':53 ''100'':22 ''fit'':46 ''get'':76 ''hat'':14 ''hay'':86 ''men'':10 ''tam'':15 ''tri'':7 ''way'':69 ''caus'':58 ''ladi'':66 ''line'':30 ''make'':85 ''near'':67 ''nice'':44 ''roll'':42 ''soon'':81 ''true'':9 ''wear'':40 ''wool'':23 ''antsi'':54 ''basqu'':2A,17 ''beret'':3A,13,18,35,78 ''black'':1A ''catch'':61 ''craft'':21 ''delay'':74 ''fanci'':63 ''great'':37 ''pleas'':71 ''today'':79 ''wanna'':60 ''water'':32 ''pantsi'':57 ''pocket'':49 ''resist'':33 ''classic'':12 ''comfort'':27 ''leather'':28 ''superbl'':20 ''everyday'':39 ''sweatband'':29');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (24, 'Cotton Beret', 'The Parkhurst SunGuard line of headwear offers the comfort and breathability of cotton and provides up to 50 times your natural protection from the sun''s rays. Fashionable, durable, and washable, Sunguard is the only choice.', '12.95', '7.95', 'CottonBeret.jpg', 'CottonBeret.thumb.jpg', 2, '''50'':20 ''ray'':29 ''sun'':27 ''line'':6 ''time'':21 ''beret'':2A ''choic'':38 ''natur'':23 ''offer'':9 ''cotton'':1A,15 ''durabl'':31 ''provid'':17 ''comfort'':11 ''fashion'':30 ''protect'':24 ''washabl'':33 ''headwear'':8 ''sunguard'':5,34 ''breathabl'':13 ''parkhurst'':4');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (25, 'Wool Beret', 'This classic tam from Kangol is one size fits all. It''s composed of 100% wool and measures 11" in diameter.', '24.99', '0.00', 'WoolBeret.jpg', 'WoolBeret.thumb.jpg', 0, '''11'':21 ''100'':17 ''fit'':11 ''one'':9 ''tam'':5 ''size'':10 ''wool'':1A,18 ''beret'':2A ''compos'':15 ''diamet'':23 ''kangol'':7 ''measur'':20 ''classic'':4');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (26, 'Military Beret', 'As one of our best selling berets, this Black Military Beret is especially popular in these war-torn days. Made of wool felt, it''s a facsimile of what Monty wore in the deserts of Africa in World War II. We don''t guarantee any sweeping victories with this beret, but you might score a personal triumph or two!', '19.99', '12.95', 'MilitaryBeret.jpg', 'MilitaryBeret.thumb.jpg', 3, '''ii'':43 ''day'':22 ''one'':4 ''two'':62 ''war'':20,42 ''best'':7 ''felt'':26 ''made'':23 ''sell'':8 ''torn'':21 ''wool'':25 ''wore'':34 ''beret'':2A,9,13,53 ''black'':11 ''might'':56 ''monti'':33 ''score'':57 ''sweep'':49 ''world'':41 ''africa'':39 ''desert'':37 ''especi'':15 ''person'':59 ''popular'':16 ''triumph'':60 ''victori'':50 ''facsimil'':30 ''guarante'':47 ''militari'':1A,12 ''war-torn'':19');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (27, 'Bond-Leather Driver', 'Leather was never so stylish. The Bond-Driver has an elastic sweatband for a sure fit. Seamed and unlined, this driver is perfect for cruising around town or saving the world.', '49.99', '0.00', 'Bond-LeatherDriver.jpg', 'Bond-LeatherDriver.thumb.jpg', 0, '''fit'':21 ''bond'':2A,12 ''save'':34 ''seam'':22 ''sure'':20 ''town'':32 ''cruis'':30 ''elast'':16 ''never'':7 ''unlin'':24 ''world'':36 ''around'':31 ''driver'':4A,13,26 ''leather'':3A,5 ''perfect'':28 ''stylish'':9 ''bond-driv'':11 ''sweatband'':17 ''bond-leath'':1A');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (28, 'Moleskin Driver', 'This quality ivy cap made by Christy''s comes with a finished lining. The material of this ivy is called moleskin and is very soft. If your life''s kinda in a hole ... and you wish you had a little more soul ... no need to beat your head against a pole, Ken ... just purchase our Christy''s Ivy Cap in Moleskin!', '29.99', '25.00', 'MoleskinDriver.jpg', 'MoleskinDriver.thumb.jpg', 2, '''cap'':6,61 ''ivi'':5,20,60 ''ken'':54 ''beat'':48 ''call'':22 ''come'':11 ''head'':50 ''hole'':35 ''life'':30 ''line'':15 ''made'':7 ''need'':46 ''pole'':53 ''soft'':27 ''soul'':44 ''wish'':38 ''kinda'':32 ''littl'':42 ''driver'':2A ''finish'':14 ''materi'':17 ''christi'':9,58 ''purchas'':56 ''qualiti'':4 ''moleskin'':1A,23,63');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (29, 'Herringbone English Driver', 'Herringbone is everywhere this year from head to toe. The English Driver ivy cap is made of wool with a cotton sweatband on the inside.', '29.99', '0.00', 'HerringboneEnglishDriver.jpg', 'HerringboneEnglishDriver.thumb.jpg', 0, '''cap'':17 ''ivi'':16 ''toe'':12 ''head'':10 ''made'':19 ''wool'':21 ''year'':8 ''insid'':28 ''cotton'':24 ''driver'':3A,15 ''english'':2A,14 ''everywher'':6 ''sweatband'':25 ''herringbon'':1A,4');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (30, 'Confederate Civil War Kepi', 'Rebel Hat - Southern Hat - This kepi comes with the crossed musket emblem on the front.', '14.99', '0.00', 'ConfederateCivilWarKepi.jpg', 'ConfederateCivilWarKepi.thumb.jpg', 0, '''hat'':6,8 ''war'':3A ''come'':11 ''kepi'':4A,10 ''civil'':2A ''cross'':14 ''front'':19 ''rebel'':5 ''emblem'':16 ''musket'':15 ''confeder'':1A ''southern'':7');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (31, 'Hillbilly Hat', 'Blocked wool, with a rope band. Please allow 1-2 weeks for delivery. Some sizes available for immediate shipment. Corn Cob pipe not included! So, go ahead Joe, or Carl, or Billy ... act nutso and be silly ... because we''ve got you covered willy-nilly ... in our sleepy-hollow Hillbilly!', '139.99', '124.95', 'HillbillyHat.jpg', 'HillbillyHat.thumb.jpg', 2, '''1'':12 ''2'':13 ''go'':29 ''ve'':43 ''1-2'':11 ''act'':36 ''cob'':24 ''got'':44 ''hat'':2A ''joe'':31 ''band'':8 ''carl'':33 ''corn'':23 ''pipe'':25 ''rope'':7 ''size'':18 ''week'':14 ''wool'':4 ''ahead'':30 ''allow'':10 ''avail'':19 ''billi'':35 ''block'':3 ''cover'':46 ''nilli'':49 ''nutso'':37 ''pleas'':9 ''silli'':40 ''willi'':48 ''hollow'':54 ''immedi'':21 ''includ'':27 ''sleepi'':53 ''deliveri'':16 ''shipment'':22 ''hillbilli'':1A,55 ''willy-nilli'':47 ''sleepy-hollow'':52');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (32, 'Mother Goose Hat', 'Sorceress Witch Hat - Boil, Boil, Toil and Trouble! Mix up a pot of your best witch''s brew in this blocked wool felt hat. Available in almost all color combinations - email us for more information.', '149.99', '0.00', 'MotherGooseHat.jpg', 'MotherGooseHat.thumb.jpg', 0, '''us'':35 ''hat'':3A,6,27 ''mix'':12 ''pot'':15 ''best'':18 ''boil'':7,8 ''brew'':21 ''felt'':26 ''goos'':2A ''toil'':9 ''wool'':25 ''avail'':28 ''block'':24 ''color'':32 ''email'':34 ''witch'':5,19 ''almost'':30 ''combin'':33 ''inform'':38 ''mother'':1A ''troubl'':11 ''sorceress'':4');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (33, 'Uncle Sam Top Hat', 'Patriotic Hats, Uncle Sam Top Hat - This silk topper is a show stopper. Hand-fashioned quality will transform you into a Yankee Doodle Dandy ... Or you can call us a macaroni (something like that).', '199.00', '175.00', 'UncleSamTopHat.jpg', 'UncleSamTopHat.thumb.jpg', 2, '''us'':34 ''hat'':4A,6,10 ''sam'':2A,8 ''top'':3A,9 ''call'':33 ''hand'':19 ''like'':38 ''show'':16 ''silk'':12 ''uncl'':1A,7 ''dandi'':29 ''doodl'':28 ''yanke'':27 ''someth'':37 ''topper'':13 ''fashion'':20 ''patriot'':5 ''qualiti'':21 ''stopper'':17 ''macaroni'':36 ''transform'':23 ''hand-fashion'':18');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (34, 'Velvet Sombrero Hat', 'Ay Caramba! This is the real thing! Get into this velvet sombrero, which is richly embossed with sequins. Comes in red and black.', '79.99', '0.00', 'VelvetSombreroHat.jpg', 'VelvetSombreroHat.thumb.jpg', 0, '''ay'':4 ''get'':11 ''hat'':3A ''red'':24 ''come'':22 ''real'':9 ''rich'':18 ''black'':26 ''thing'':10 ''emboss'':19 ''sequin'':21 ''velvet'':1A,14 ''caramba'':5 ''sombrero'':2A,15');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (35, 'Conductor Hat', 'Train Railroad Conductor Hat - You been working on the railroad all the live-long day? Well now, you can wear our Conductor''s hat, and your troubles will all go away! We sell a ton of these! Set the scene correctly with an authentic train or streetcar conductor''s uniform hat. Also makes a great gift for transportation enthusiasts. Don''t be a drain ... get on the train!', '69.99', '0.00', 'ConductorHat.jpg', 'ConductorHat.thumb.jpg', 0, '''go'':33 ''day'':18 ''get'':68 ''hat'':2A,6,27,54 ''set'':41 ''ton'':38 ''also'':55 ''away'':34 ''gift'':59 ''live'':16 ''long'':17 ''make'':56 ''sell'':36 ''wear'':23 ''well'':19 ''work'':9 ''drain'':67 ''great'':58 ''scene'':43 ''train'':3,48,71 ''troubl'':30 ''authent'':47 ''correct'':44 ''uniform'':53 ''railroad'':4,12 ''conductor'':1A,5,25,51 ''live-long'':15 ''streetcar'':50 ''transport'':61 ''enthusiast'':62');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (36, 'Traditional Colonial Tricorn Hat', 'Truly revolutionary headgear. This hat is blocked from black wool, and edges are finished with white ribbon. The edges are tacked up for durability. So if you''re glad to be born ... if you wanna toot your own horn ... just hop out of bed some lovely morn ... and put on our Traditional Colonial Tricorn!', '39.99', '0.00', 'TraditionalColonialTricornHat.jpg', 'TraditionalColonialTricornHat.thumb.jpg', 0, '''re'':32 ''bed'':48 ''edg'':16,23 ''hat'':4A,9 ''hop'':45 ''put'':53 ''born'':36 ''glad'':33 ''horn'':43 ''love'':50 ''morn'':51 ''tack'':25 ''toot'':40 ''wool'':14 ''black'':13 ''block'':11 ''truli'':5 ''wanna'':39 ''white'':20 ''coloni'':2A,57 ''durabl'':28 ''finish'':18 ''ribbon'':21 ''tradit'':1A,56 ''tricorn'':3A,58 ''headgear'':7 ''revolutionari'':6');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (37, 'Metal Viking Helmet', 'You can almost hear the creaking oars of your warship as you glide across open seas! Conquer new worlds with this authentic Nordic reproduction. Crafted from metal and horn, the Viking helmet is a necessity for any adventure. Would you adorn it while biking? ... How about on the wooded trials while hiking? ... Betcha it''ll always be to your liking ... wherever you wear our Metal Viking!', '119.99', '105.95', 'MetalVikingHelmet.jpg', 'MetalVikingHelmet.thumb.jpg', 2, '''ll'':58 ''new'':21 ''oar'':10 ''sea'':19 ''bike'':47 ''hear'':7 ''hike'':55 ''horn'':32 ''like'':63 ''open'':18 ''vike'':2A,34,69 ''wear'':66 ''wood'':52 ''adorn'':44 ''alway'':59 ''craft'':28 ''creak'':9 ''glide'':16 ''metal'':1A,30,68 ''trial'':53 ''world'':22 ''would'':42 ''across'':17 ''almost'':6 ''betcha'':56 ''helmet'':3A,35 ''necess'':38 ''nordic'':26 ''wherev'':64 ''authent'':25 ''conquer'':20 ''warship'':13 ''adventur'':41 ''reproduct'':27');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (38, 'Confederate Slouch Hat', 'Our replica Confederate Slouch Hat from the Civil War comes with Cavalry yellow straps and crossed-sword emblem.', '129.99', '101.99', 'ConfederateSlouchHat.jpg', 'ConfederateSlouchHat.thumb.jpg', 1, '''hat'':3A,8 ''war'':12 ''come'':13 ''civil'':11 ''cross'':20 ''strap'':17 ''sword'':21 ''emblem'':22 ''slouch'':2A,7 ''yellow'':16 ''cavalri'':15 ''replica'':5 ''confeder'':1A,6 ''crossed-sword'':19');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (39, 'Campaign Hat', 'Dress the part of Dudley-Do-Right, State Trooper Bob, Smokey the Bear, or WWI Doughboy. Wear it in the rain ... wear it carrying a cane ... wear it if you''re crazy or sane ... just wear our versatile Campaign!', '44.99', '0.00', 'CampaignHat.jpg', 'CampaignHat.thumb.jpg', 0, '''re'':34 ''bob'':13 ''hat'':2A ''wwi'':18 ''bear'':16 ''cane'':29 ''part'':5 ''rain'':24 ''sane'':37 ''wear'':20,25,30,39 ''carri'':27 ''crazi'':35 ''dress'':3 ''right'':10 ''state'':11 ''dudley'':8 ''smokey'':14 ''trooper'':12 ''campaign'':1A,42 ''doughboy'':19 ''versatil'':41 ''dudley-do-right'':7');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (40, 'Civil War Union Slouch Hat', 'This Yankee slouch hat from the Civil War era comes in a black wool felt material and has the U.S. metal emblem on the front. This Union hat comes with the officer''s cords.', '129.99', '0.00', 'CivilWarUnionSlouchHat.jpg', 'CivilWarUnionSlouchHat.thumb.jpg', 0, '''era'':14 ''hat'':5A,9,33 ''u.s'':25 ''war'':2A,13 ''come'':15,34 ''cord'':39 ''felt'':20 ''wool'':19 ''black'':18 ''civil'':1A,12 ''front'':30 ''metal'':26 ''offic'':37 ''union'':3A,32 ''yanke'':7 ''emblem'':27 ''materi'':21 ''slouch'':4A,8');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (41, 'Civil War Leather Kepi Cap', 'Calling all Civil War buffs! Yanks grab the blue, and Rebs get the gray. You''ll all be victorious in this suede cap worn in the "War Between the States." So, if on the Civil War you''re hep-eee ... then by all means, you gotta have our kepi!', '39.99', '0.00', 'CivilWarLeatherKepiCap.jpg', 'CivilWarLeatherKepiCap.thumb.jpg', 0, '''ll'':21 ''re'':43 ''cap'':5A,28 ''eee'':46 ''get'':17 ''hep'':45 ''reb'':16 ''war'':2A,9,32,41 ''blue'':14 ''buff'':10 ''call'':6 ''grab'':12 ''gray'':19 ''kepi'':4A,55 ''mean'':50 ''sued'':27 ''worn'':29 ''yank'':11 ''civil'':1A,8,40 ''gotta'':52 ''state'':35 ''hep-ee'':44 ''leather'':3A ''victori'':24');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (42, 'Cavalier Hat - Three Musketeers', 'Reproduction of the original Cavalier hat complete with a feather! Handblocked from 100% wool felt. This is as close to the real thing as you get. It is better than downing a beer ... it is better than having your honey say, "Come on over here, Dear" ... All you gotta do is let go of your fear ... and order this stunning, galant Cavalier!', '185.00', '0.00', 'CavalierHat-ThreeMusketeers.jpg', 'CavalierHat-ThreeMusketeers.thumb.jpg', 1, '''go'':57 ''100'':17 ''get'':30 ''hat'':2A,10 ''let'':56 ''say'':45 ''beer'':37 ''come'':46 ''dear'':50 ''down'':35 ''fear'':60 ''felt'':19 ''real'':26 ''stun'':64 ''wool'':18 ''close'':23 ''gotta'':53 ''honey'':44 ''order'':62 ''thing'':27 ''three'':3A ''better'':33,40 ''cavali'':1A,9,66 ''galant'':65 ''musket'':4A ''origin'':8 ''complet'':11 ''feather'':14 ''handblock'':15 ''reproduct'':5');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (43, 'Hussar Military Hat', 'A "Hussar" was an enlisted Cavalry soldier. All hussar soldiers were taught to read and write, and they commonly kept journals of some sort - probably helping them to pass the time while they were away from home in the service of their country. They were required to keep records of their duties and work, as well.', '199.99', '0.00', 'HussarMilitaryHat.jpg', 'HussarMilitaryHat.thumb.jpg', 0, '''hat'':3A ''away'':38 ''duti'':55 ''help'':29 ''home'':40 ''keep'':51 ''kept'':23 ''pass'':32 ''read'':17 ''sort'':27 ''time'':34 ''well'':59 ''work'':57 ''write'':19 ''common'':22 ''enlist'':8 ''hussar'':1A,5,12 ''record'':52 ''requir'':49 ''servic'':43 ''taught'':15 ''cavalri'':9 ''countri'':46 ''journal'':24 ''probabl'':28 ''soldier'':10,13 ''militari'':2A');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (44, 'Union Civil War Kepi Cap', 'Union Soldier''s Cap - Yankee Cap - This kepi comes with the crossed musket emblem on the front.', '14.99', '0.00', 'UnionCivilWarKepiCap.jpg', 'UnionCivilWarKepiCap.thumb.jpg', 2, '''cap'':5A,9,11 ''war'':3A ''come'':14 ''kepi'':4A,13 ''civil'':2A ''cross'':17 ''front'':22 ''union'':1A,6 ''yanke'':10 ''emblem'':19 ''musket'':18 ''soldier'':7');
INSERT INTO product (product_id, name, description, price, discounted_price, image, thumbnail, display, search_vector)
       VALUES (45, 'Tarbucket Helmet Military Hat', 'This is a reproduction Tarbucket type hat. This style was a popular military style in the early to mid 1800s. The style is similar to a shako hat, with the main difference being that the crown flairs outward.', '299.99', '0.00', 'TarbucketHelmetMilitaryHat.jpg', 'TarbucketHelmetMilitaryHat.thumb.jpg', 0, '''hat'':4A,11,32 ''mid'':23 ''main'':35 ''type'':10 ''1800s'':24 ''crown'':40 ''earli'':21 ''flair'':41 ''shako'':31 ''style'':13,18,26 ''differ'':36 ''helmet'':2A ''outward'':42 ''popular'':16 ''similar'':28 ''militari'':3A,17 ''reproduct'':8 ''tarbucket'':1A,9');

-- Update the sequence
ALTER SEQUENCE product_product_id_seq RESTART WITH 46;

-- Populate product_category table
INSERT INTO product_category VALUES (1, 1);
INSERT INTO product_category VALUES (2, 1);
INSERT INTO product_category VALUES (3, 1);
INSERT INTO product_category VALUES (4, 1);
INSERT INTO product_category VALUES (5, 1);
INSERT INTO product_category VALUES (6, 1);
INSERT INTO product_category VALUES (7, 1);
INSERT INTO product_category VALUES (8, 2);
INSERT INTO product_category VALUES (9, 2);
INSERT INTO product_category VALUES (10, 2);
INSERT INTO product_category VALUES (11, 2);
INSERT INTO product_category VALUES (12, 2);
INSERT INTO product_category VALUES (13, 2);
INSERT INTO product_category VALUES (14, 2);
INSERT INTO product_category VALUES (15, 2);
INSERT INTO product_category VALUES (16, 3);
INSERT INTO product_category VALUES (17, 3);
INSERT INTO product_category VALUES (18, 3);
INSERT INTO product_category VALUES (19, 3);
INSERT INTO product_category VALUES (20, 3);
INSERT INTO product_category VALUES (21, 3);
INSERT INTO product_category VALUES (22, 3);
INSERT INTO product_category VALUES (23, 4);
INSERT INTO product_category VALUES (24, 4);
INSERT INTO product_category VALUES (25, 4);
INSERT INTO product_category VALUES (26, 4);
INSERT INTO product_category VALUES (8, 5);
INSERT INTO product_category VALUES (27, 5);
INSERT INTO product_category VALUES (28, 5);
INSERT INTO product_category VALUES (29, 5);
INSERT INTO product_category VALUES (30, 6);
INSERT INTO product_category VALUES (31, 6);
INSERT INTO product_category VALUES (32, 6);
INSERT INTO product_category VALUES (33, 6);
INSERT INTO product_category VALUES (34, 6);
INSERT INTO product_category VALUES (35, 6);
INSERT INTO product_category VALUES (26, 7);
INSERT INTO product_category VALUES (36, 7);
INSERT INTO product_category VALUES (37, 7);
INSERT INTO product_category VALUES (38, 7);
INSERT INTO product_category VALUES (39, 7);
INSERT INTO product_category VALUES (40, 7);
INSERT INTO product_category VALUES (41, 7);
INSERT INTO product_category VALUES (42, 7);
INSERT INTO product_category VALUES (43, 7);
INSERT INTO product_category VALUES (44, 7);
INSERT INTO product_category VALUES (45, 7);

-- Populate shipping_region table
INSERT INTO shipping_region (shipping_region_id, shipping_region)
       VALUES (1, 'Please Select');
INSERT INTO shipping_region (shipping_region_id, shipping_region)
       VALUES (2, 'US / Canada');
INSERT INTO shipping_region (shipping_region_id, shipping_region)
       VALUES (3, 'Europe');
INSERT INTO shipping_region (shipping_region_id, shipping_region)
       VALUES (4, 'Rest of World');

-- Update the sequence
ALTER SEQUENCE shipping_region_shipping_region_id_seq RESTART WITH 5;

-- Populate shipping table
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(1, 'Next Day Delivery ($20)', 20.00, 2);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(2, '3-4 Days ($10)', 10.00, 2);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(3, '7 Days ($5)', 5.00, 2);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(4, 'By air (7 days, $25)', 25.00, 3);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(5, 'By sea (28 days, $10)', 10.00, 3);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(6, 'By air (10 days, $35)', 35.00, 4);
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(7, 'By sea (28 days, $30)', 30.00, 4);

-- Update the sequence
ALTER SEQUENCE shipping_shipping_id_seq RESTART WITH 8;

-- Populate tax table
INSERT INTO tax (tax_id, tax_type, tax_percentage)
       VALUES(1, 'Sales Tax at 8.5%', 8.50);
INSERT INTO tax (tax_id, tax_type, tax_percentage)
       VALUES(2, 'No Tax', 0.00);

-- Update the sequence
ALTER SEQUENCE tax_tax_id_seq RESTART WITH 3;
