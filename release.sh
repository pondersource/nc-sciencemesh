echo run this file as www-data user inside a Docker container
echo first, docker exec as root and chown /var/www/html/apps/sciencemesh to www-user
echo then, docker exec as www-user, and store sciencemesh.key into /var/www/sciencemesh.key (make sure to remove it or delete the container after you're done)
echo then cd into apps/sciencemesh, and run this script.

mkdir -p build/sciencemesh
rm -rf build/sciencemesh/*
cp -r appinfo build/sciencemesh/
cp -r css build/sciencemesh/
cp -r img build/sciencemesh/
cp -r js build/sciencemesh/
cp -r lib build/sciencemesh/
cp -r templates build/sciencemesh/
cp -r composer.* build/sciencemesh/
cd build/sciencemesh/
composer install
cd /var/www/html
./occ integrity:sign-app --privateKey=/var/www/sciencemesh.key --certificate=apps/sciencemesh/sciencemesh.crt --path=apps/sciencemesh/build/sciencemesh
cd apps/sciencemesh/build
tar -cf sciencemesh.tar sciencemesh
cd ../release
mv ../build/sciencemesh.tar .
rm -f -- sciencemesh.tar.gz
gzip sciencemesh.tar
cd ..
