delete from reports;
delete from wishlists;
delete from transactions;
delete from ratings;
delete from bargains;
delete from product_images;
delete from products;
delete from categories;
delete from users;

insert into users (
   id,
   name,
   email,
   password_hash,
   mobile_no
) values ( users_id_seq.nextval,
           'Shomik Shahriar',
           'shomik@stud.kuet.ac.bd',
           '$2y$12$jrHaveK490wOTgsP7hTtRu9I.maAlmIpjadnG2MGsmU/a7r9fguZK',
           '01700000001' );

insert into users (
   id,
   name,
   email,
   password_hash,
   mobile_no
) values ( users_id_seq.nextval,
           'Ayesha Rahman',
           'ayesha@stud.kuet.ac.bd',
           '$2y$12$jrHaveK490wOTgsP7hTtRu9I.maAlmIpjadnG2MGsmU/a7r9fguZK',
           '01700000002' );

insert into users (
   id,
   name,
   email,
   password_hash,
   mobile_no
) values ( users_id_seq.nextval,
           'Rafiul Islam',
           'rafiul@stud.kuet.ac.bd',
           '$2y$12$jrHaveK490wOTgsP7hTtRu9I.maAlmIpjadnG2MGsmU/a7r9fguZK',
           '01700000003' );

insert into users (
   id,
   name,
   email,
   password_hash,
   mobile_no
) values ( users_id_seq.nextval,
           'Nusrat Jahan',
           'nusrat@stud.kuet.ac.bd',
           '$2y$12$jrHaveK490wOTgsP7hTtRu9I.maAlmIpjadnG2MGsmU/a7r9fguZK',
           '01700000004' );

insert into users (
   id,
   name,
   email,
   password_hash,
   mobile_no
) values ( users_id_seq.nextval,
           'Tanvir Ahmed',
           'tanvir@stud.kuet.ac.bd',
           '$2y$12$jrHaveK490wOTgsP7hTtRu9I.maAlmIpjadnG2MGsmU/a7r9fguZK',
           '01700000005' );

insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Books' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Laptop' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Mobile' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Electronics' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Furniture' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Cycle' );
insert into categories (
   id,
   category_name
) values ( categories_id_seq.nextval,
           'Calculator' );

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Laptop'
          ),
          'Dell Latitude 5490 Laptop',
          'Lightly used office laptop, Core i5 / 8GB RAM.',
          'OLD',
          24000,
          'AVAILABLE'
     from dual;

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'ayesha@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Calculator'
          ),
          'Casio fx-991EX Calculator',
          'Brand new scientific calculator, sealed box.',
          'NEW',
          1200,
          'SOLD'
     from dual;

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Books'
          ),
          'Data Structures Textbook',
          'Good condition, minimal highlights.',
          'OLD',
          350,
          'AVAILABLE'
     from dual;

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Cycle'
          ),
          'Phoenix Hercules Cycle',
          'Single speed, recently serviced.',
          'OLD',
          5500,
          'AVAILABLE'
     from dual;

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'ayesha@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Mobile'
          ),
          'Redmi Note 12 Mobile',
          'No scratches, with box and charger.',
          'OLD',
          14000,
          'AVAILABLE'
     from dual;

insert into products (
   id,
   seller_id,
   category_id,
   title,
   description,
   product_condition,
   min_proposed_price,
   status
)
   select products_id_seq.nextval,
          (
             select id
               from users
              where email = 'tanvir@stud.kuet.ac.bd'
          ),
          (
             select id
               from categories
              where category_name = 'Furniture'
          ),
          'Wooden Study Table',
          'Sturdy table, small scratch on top.',
          'OLD',
          2000,
          'UNAVAILABLE'
     from dual;

insert into product_images (
   id,
   product_id,
   image_path
)
   select product_images_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          'products/dell_1.jpg'
     from dual;

insert into product_images (
   id,
   product_id,
   image_path
)
   select product_images_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          'products/dell_2.jpg'
     from dual;

insert into product_images (
   id,
   product_id,
   image_path
)
   select product_images_id_seq.nextval,
          (
             select id
               from products
              where title = 'Redmi Note 12 Mobile'
          ),
          'products/redmi_1.jpg'
     from dual;

-- BARGAINS (bids)
insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          24250,
          'PENDING'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          24500,
          'PENDING'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Dell Latitude 5490 Laptop'
          ),
          (
             select id
               from users
              where email = 'tanvir@stud.kuet.ac.bd'
          ),
          23000,
          'PENDING'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          1500,
          'ACCEPTED'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          1400,
          'REJECTED'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'tanvir@stud.kuet.ac.bd'
          ),
          1350,
          'REJECTED'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Data Structures Textbook'
          ),
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          400,
          'PENDING'
     from dual;

insert into bargains (
   id,
   product_id,
   buyer_id,
   bid_amount,
   bid_status
)
   select bargains_id_seq.nextval,
          (
             select id
               from products
              where title = 'Redmi Note 12 Mobile'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          14200,
          'PENDING'
     from dual;

insert into transactions (
   id,
   product_id,
   buyer_id,
   final_price
)
   select transactions_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          1500
     from dual;

insert into wishlists (
   id,
   user_id,
   product_id
)
   select wishlists_id_seq.nextval,
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          )
     from dual;

insert into wishlists (
   id,
   user_id,
   product_id
)
   select wishlists_id_seq.nextval,
          (
             select id
               from users
              where email = 'tanvir@stud.kuet.ac.bd'
          ),
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          )
     from dual;

insert into wishlists (
   id,
   user_id,
   product_id
)
   select wishlists_id_seq.nextval,
          (
             select id
               from users
              where email = 'shomik@stud.kuet.ac.bd'
          ),
          (
             select id
               from products
              where title = 'Redmi Note 12 Mobile'
          )
     from dual;

insert into ratings (
   id,
   product_id,
   rater_id,
   rated_user_id,
   rating_type,
   rating_value,
   review_text
)
   select ratings_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'ayesha@stud.kuet.ac.bd'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          'BUYER_RATING',
          5,
          'Smooth and punctual buyer.'
     from dual;

insert into ratings (
   id,
   product_id,
   rater_id,
   rated_user_id,
   rating_type,
   rating_value,
   review_text
)
   select ratings_id_seq.nextval,
          (
             select id
               from products
              where title = 'Casio fx-991EX Calculator'
          ),
          (
             select id
               from users
              where email = 'rafiul@stud.kuet.ac.bd'
          ),
          (
             select id
               from users
              where email = 'ayesha@stud.kuet.ac.bd'
          ),
          'SELLER_RATING',
          4,
          'Calculator exactly as described, good deal.'
     from dual;

insert into reports (
   id,
   reporter_id,
   reported_id,
   product_id,
   report_type,
   reason
)
   select reports_id_seq.nextval,
          (
             select id
               from users
              where email = 'nusrat@stud.kuet.ac.bd'
          ),
          (
             select id
               from users
              where email = 'tanvir@stud.kuet.ac.bd'
          ),
          (
             select id
               from products
              where title = 'Wooden Study Table'
          ),
          'SELLER',
          'Listing photo looks suspicious / possibly fake.'
     from dual;

commit;